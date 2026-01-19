<?php

/**
 * Created by Vextras.
 *
 * Name: Ryan Hungate
 * Email: ryan@vextras.com
 * Date: 11/14/16
 * Time: 9:38 AM
 */
class SqualoMail_WooCommerce_User_Submit extends Squalomail_Woocommerce_Job
{
    public static $handling_for = null;

    public $id;
    public $subscribed;
    public $gdpr_fields;
    public $updated_data;
    public $language;
    public $should_ignore = false;

    /**
     * SqualoMail_WooCommerce_User_Submit constructor.
     * @param null $id
     * @param null $subscribed
     * @param WP_User|null $updated_data
     */
    public function __construct($id = null, $subscribed = null, $updated_data = null, $language = null, $gdpr_fields = null)
    {
        if (!empty($id)) {
            // if we're passing in another user with the same id during the same php process we need to ignore it.
            if (static::$handling_for === $id) {
                $this->should_ignore = true;
            }
            // set the user id and the current 'handling_for' to this user id so we don't duplicate jobs.
            static::$handling_for = $this->id = $id;
        }

        if (is_bool($subscribed)) {
            $this->subscribed = $subscribed;
            
            if ($subscribed && !empty($gdpr_fields)) {
                foreach ($gdpr_fields as $id => $value) {
                    $gdpr_field['marketing_permission_id'] = $id;
                    $gdpr_field['enabled'] = (bool) $value;
                    $this->gdpr_fields[] = $gdpr_field;
                }
            }
        }

        

        if (!empty($updated_data)) {
            $this->updated_data = $updated_data->to_array();
        }

        if (!empty($language)) {
            $this->language = $language;
        }
    }

    /**
     * @return bool
     */
    public function handle()
    {
        if (!squalomail_is_configured()) {
            squalomail_debug(get_called_class(), 'Squalo is not configured properly');
            static::$handling_for = null;
            return false;
        }

        if ($this->should_ignore) {
            squalomail_debug(get_called_class(), "{$this->id} is currently in motion - skipping this one.");
            static::$handling_for = null;
            return false;
        }

        $options = get_option('squalomail-woocommerce', array());
        $store_id = squalomail_get_store_id();

        // load up the user.
        $user = new WP_User($this->id);

        // we need a valid user, a valid store id and options to continue
        if ($user->ID <= 0 || empty($store_id) || !is_array($options)) {

            // seems as if the database records are not being set by the time this queue job is fired,
            // just a precautionary to make sure it's available during
            sleep(1);

            $options = get_option('squalomail-woocommerce', array());
            $store_id = squalomail_get_store_id();

            // load up the user.
            $user = new WP_User($this->id);

            if ($user->ID <= 0 || empty($store_id) || !is_array($options)) {
                squalomail_log('member.sync', "Invalid Data For Submission :: {$user->ID}");
                static::$handling_for = null;
                return false;
            }
        }

        $email = $user->user_email;

        // make sure we don't need to skip this email
        if (!squalomail_email_is_allowed($email)) {
            static::$handling_for = null;
            return false;
        }

        // if we have a null value, we need to grab the correct user meta for is_subscribed
        if (is_null($this->subscribed)) {
            $user_subscribed = get_user_meta($this->id, 'squalomail_woocommerce_is_subscribed', true);
            if ($user_subscribed === '' || $user_subscribed === null) {
                squalomail_log('member.sync', "Skipping sync for {$email} because no subscriber status has been set");
                static::$handling_for = null;
                return false;
            }
            $this->subscribed = (bool) $user_subscribed;
        }

        $api_key = isset($options['squalomail_api_key']) ? $options['squalomail_api_key'] : false;
        $list_id = isset($options['squalomail_list']) ? $options['squalomail_list'] : false;

        // we need a valid api key and list id to continue
        if (empty($api_key) || empty($list_id)) {
            squalomail_log('member.sync', "Invalid Api Key or ListID :: {$email}");
            static::$handling_for = null;
            return false;
        }

        // don't let anyone be unsubscribed from the list - that should only happen on email campaigns
        // and someone clicking the unsubscribe linkage.
        if (!$this->subscribed) {
            static::$handling_for = null;
            return false;
        }

        $api = new SqualoMail_WooCommerce_SqualoMailApi($api_key);

        $merge_fields_system = array();

        $fn = trim($user->first_name);
        $ln = trim($user->last_name);

        if (!empty($fn)) $merge_fields_system['FNAME'] = $fn;
        if (!empty($ln)) $merge_fields_system['LNAME'] = $ln;

        // allow users to hook into the merge field submission
        $merge_fields = apply_filters('squalomail_sync_user_mergetags', $merge_fields_system, $user);

        // for whatever reason if this isn't an array we need to skip it.
        if (!is_array($merge_fields)) {
            squalomail_error("custom.merge_fields", "The filter for squalomail_sync_user_mergetags needs to return an array, using the default setup instead.");
            $merge_fields = $merge_fields_system;
        }
        // language
        $language = $this->language;
        
        // GDPR
        $gdpr_fields = $this->gdpr_fields;

        // pull the transient key for this job.
        $transient_id = squalomail_get_transient_email_key($email);
        $status_meta = squalomail_get_subscriber_status_options($this->subscribed);

        try {

            // check to see if the status meta has changed when a false response is given
            if (squalomail_check_serialized_transient_changed($transient_id, $status_meta) === false) {
                squalomail_debug(get_called_class(), "Skipping sync for {$email} because it was just pushed less than a minute ago.");
                static::$handling_for = null;
                return false;
            }

            // see if we have a member.
            $member_data = $api->member($list_id, $email);

            // if we're updating a member and the email is different, we need to delete the old person
            if (is_array($this->updated_data) && isset($this->updated_data['user_email'])) {
                if ($this->updated_data['user_email'] !== $email) {
                    // delete the old
                    $api->deleteMember($list_id, $this->updated_data['user_email']);
                    // subscribe the new
                    $api->subscribe($list_id, $email, $status_meta['created'], $merge_fields, null, $language, $gdpr_fields);

                    // update the member tags but fail silently just in case.
                    $api->updateMemberTags(squalomail_get_list_id(), $email, true);

                    squalomail_tell_system_about_user_submit($email, $status_meta, 60);

                    if ($status_meta['created']) {
                        squalomail_log('member.sync', 'Subscriber Swap '.$this->updated_data['user_email'].' to '.$email, array(
                            'status' => $status_meta['created'],
                            'merge_fields' => $merge_fields
                        ));
                    } else {
                        squalomail_log('member.sync', 'Subscriber Swap '.$this->updated_data['user_email'].' to '.$email.' Pending Double OptIn', array(
                            'status' => $status_meta['created'],
                            'merge_fields' => $merge_fields
                        ));
                    }
                    static::$handling_for = null;
                    return false;
                }
            }

            // if the member is unsubscribed or pending, we really can't do anything here.
            if (isset($member_data['status']) && in_array($member_data['status'], array('unsubscribed', 'pending'))) {
                squalomail_log('member.sync', "Skipped Member Sync For {$email} because the current status is {$member_data['status']}", $merge_fields);
                static::$handling_for = null;
                return false;
            }

            // if the status is not === 'transactional' we can update them to subscribed or pending now.
            if (isset($member_data['status']) && $member_data['status'] === 'transactional' || $member_data['status'] === 'cleaned') {
                // ok let's update this member
                $api->update($list_id, $email, $status_meta['updated'], $merge_fields, null, $language, $gdpr_fields);
                
                // update the member tags but fail silently just in case.
                $api->updateMemberTags(squalomail_get_list_id(), $email, true);

                squalomail_tell_system_about_user_submit($email, $status_meta, 60);
                squalomail_log('member.sync', "Updated Member {$email}", array(
                    'previous_status' => $member_data['status'],
                    'status' => $status_meta['updated'],
                    'merge_fields' => $merge_fields
                ));
                static::$handling_for = null;
                return true;
            }

            if (isset($member_data['status'])) {
                // ok let's update this member
                $api->update($list_id, $email, $member_data['status'], $merge_fields, null, $language, $gdpr_fields);

                // update the member tags but fail silently just in case.
                $api->updateMemberTags(squalomail_get_list_id(), $email, true);

                squalomail_tell_system_about_user_submit($email, $status_meta, 60);
                squalomail_log('member.sync', "Updated Member {$email} ( merge fields only )", array(
                    'merge_fields' => $merge_fields
                ));
                static::$handling_for = null;
                return true;
            }

            static::$handling_for = null;
        } catch (SqualoMail_WooCommerce_RateLimitError $e) {
            sleep(3);
            squalomail_error('member.sync.error', squalomail_error_trace($e, "RateLimited :: user #{$this->id}"));
            $this->retry();
        } catch (\Exception $e) {
            // if we have a 404 not found, we can create the member
            if ($e->getCode() == 404) {

                try {
                    $uses_doi = isset($status_meta['requires_double_optin']) && $status_meta['requires_double_optin'];
                    $status_if_new = $uses_doi ? 'pending' : true;

                    $api->subscribe($list_id, $user->user_email, $status_if_new, $merge_fields, null, $language, $gdpr_fields);
                    
                    // update the member tags but fail silently just in case.
                    $api->updateMemberTags(squalomail_get_list_id(), $email, true);

                    squalomail_tell_system_about_user_submit($email, $status_meta, 60);
                    if ($status_meta['created']) {
                        squalomail_log('member.sync', "Subscribed Member {$user->user_email}", array('status_if_new' => $status_if_new, 'merge_fields' => $merge_fields));
                    } else {
                        squalomail_log('member.sync', "{$user->user_email} is Pending Double OptIn");
                    }
                } catch (\Exception $e) {
                    squalomail_log('member.sync', $e->getMessage());
                }
                static::$handling_for = null;
                return false;
            }
            squalomail_error('member.sync', squalomail_error_trace($e, $user->user_email));
        }

        static::$handling_for = null;

        return false;
    }
}
