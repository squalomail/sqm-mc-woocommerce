<?php

// if we don't have valid campaign defaults we need to redirect back to the 'campaign_defaults' tab.
if (!$handler->hasValidApiKey()) {
    wp_redirect('admin.php?page=squalomail-woocommerce&tab=api_key&error_notice=missing_api_key');
}

// if we don't have valid store information, we need to redirect back to the 'store_info' tab.
if (!$handler->hasValidStoreInfo()) {
    wp_redirect('admin.php?page=squalomail-woocommerce&tab=store_info&error_notice=missing_store');
}

// if we don't have a valid api key we need to redirect back to the 'api_key' tab.
if (!isset($squalomail_lists) && ($squalomail_lists = $handler->getSqualoMailLists()) === false) {
    wp_redirect('admin.php?page=squalomail-woocommerce&tab=api_key&error_notice=missing_api_key');
}

// if we don't have valid campaign defaults we need to redirect back to the 'campaign_defaults' tab.
if (empty($squalomail_lists) && !$handler->hasValidCampaignDefaults()) {
    wp_redirect('admin.php?page=squalomail-woocommerce&tab=campaign_defaults&error_notice=missing_campaign_defaults');
}

$list_is_configured = isset($options['squalomail_list']) && (!empty($options['squalomail_list'])) && array_key_exists($options['squalomail_list'], $squalomail_lists);

?>

<?php if(($newsletter_settings_error = $this->getData('errors.squalomail_list', false))) : ?>
    <div class="error notice is-dismissable">
        <p><?php echo $newsletter_settings_error; ?></p>
    </div>
<?php endif; ?>

<input type="hidden" name="squalomail_active_settings_tab" value="newsletter_settings"/>
<fieldset>
    <legend class="screen-reader-text">
        <span><?php esc_html_e('Audience Settings', 'squalomail-for-woocommerce');?></span>
    </legend>

    <div class="box" >
        <label for="<?php echo $this->plugin_name; ?>-squalomail-list-label">
            <strong><?php esc_html_e('Sync audience with your store', 'squalomail-for-woocommerce'); ?></strong>
        </label>
        <div class="squalomail-select-wrapper">
            <select name="<?php echo $this->plugin_name; ?>[squalomail_list]" required <?php echo ($list_is_configured || $only_one_list) ? 'disabled' : '' ?>>

                <?php if(!isset($allow_new_list) || $allow_new_list === true): ?>
                    <option value="create_new"><?php esc_html_e('Create New Audience', 'squalomail-for-woocommerce');?></option>
                <?php endif ?>

                <?php if(isset($allow_new_list) && $allow_new_list === false): ?>
                    <option value="">-- <?php esc_html_e('Select Audience', 'squalomail-for-woocommerce');?> --</option>
                <?php endif; ?>

                <?php
                if (is_array($squalomail_lists)) {
                    $selected_list = isset($options['squalomail_list']) ? $options['squalomail_list'] : null;
                    foreach ($squalomail_lists as $key => $value ) {
                        echo '<option value="' . esc_attr( $key ) . '" ' . selected(((string) $key === (string) $selected_list || $only_one_list), true, false) . '>' . esc_html( $value ) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
    </div>

    <div class="box" >
        <?php $enable_auto_subscribe = (array_key_exists('squalomail_auto_subscribe', $options) && !is_null($options['squalomail_auto_subscribe'])) ? $options['squalomail_auto_subscribe'] : '1'; ?>
        <label>
            <input
                    type="checkbox"
                    name="<?php echo $this->plugin_name; ?>[squalomail_auto_subscribe]"
                    id="<?php echo $this->plugin_name; ?>[squalomail_auto_subscribe]"
                <?= $list_is_configured ? 'disabled': '' ?>
                    value=1
                <?= $enable_auto_subscribe ? 'checked' : ''?>>
            <strong><?php esc_html_e('During initial sync, auto subscribe the existing customers.', 'squalomail-for-woocommerce'); ?></strong>
        </label>
    </div>

    <div class="box optional-settings-label" >
        <span><?php esc_html_e('Optional Audience Settings', 'squalomail-for-woocommerce');?></span>
    </div>

    <div class="optional-settings-content">
        <div class="box fieldset-header" >
            <h3><?php esc_html_e('Opt-in Settings', 'squalomail-for-woocommerce');?></h3>
        </div>

        <div class="box box-half">
            <label for="<?php echo $this->plugin_name; ?>-newsletter-checkbox-label">
                <h4><?php esc_html_e('Message for the opt-in checkbox', 'squalomail-for-woocommerce'); ?></h4>
                <p><?php _e('The call-to-action text that prompts customers to subscribe to your newsletter at checkout.', 'squalomail-for-woocommerce');?> </p>
            </label>
        </div>

        <div class="box box-half">
            <textarea rows="3" id="<?php echo $this->plugin_name; ?>-newsletter-checkbox-label" name="<?php echo $this->plugin_name; ?>[newsletter_label]"><?php echo isset($options['newsletter_label']) ? esc_html($options['newsletter_label']) : esc_html__('Subscribe to our newsletter', 'squalomail-for-woocommerce'); ?></textarea>
            <p class="description"><?= esc_html(__('HTML tags allowed: <a href="" target="" title=""></a> and <br>', 'squalomail-for-woocommerce')); ?></p>
        </div>

        <div class="box box-half margin-large">
            <label>
                <h4><?php esc_html_e('Checkbox Display Options', 'squalomail-for-woocommerce');?></h4>
                <p><?php _e('Choose how you want the opt-in to your newsletter checkbox to render at checkout', 'squalomail-for-woocommerce');?> </p>
            </label>
        </div>


        <div class="box box-half margin-large">
            <?php $checkbox_default_settings = (array_key_exists('squalomail_checkbox_defaults', $options) && !is_null($options['squalomail_checkbox_defaults'])) ? $options['squalomail_checkbox_defaults'] : 'check'; ?>
            <label class="radio-label">
                <input type="radio" name="<?php echo $this->plugin_name; ?>[squalomail_checkbox_defaults]" value="check"<?php if($checkbox_default_settings === 'check') echo ' checked="checked" '; ?>><?php esc_html_e('Visible, checked by default', 'squalomail-for-woocommerce');?><br>
            </label>
            <label class="radio-label">
                <input type="radio" name="<?php echo $this->plugin_name; ?>[squalomail_checkbox_defaults]" value="uncheck"<?php if($checkbox_default_settings === 'uncheck') echo ' checked="checked" '; ?>><?php esc_html_e('Visible, unchecked by default', 'squalomail-for-woocommerce');?><br/>
            </label>
            <label class="radio-label">
                <input type="radio" name="<?php echo $this->plugin_name; ?>[squalomail_checkbox_defaults]" value="hide"<?php if($checkbox_default_settings === 'hide') echo ' checked="checked" '; ?>><?php esc_html_e('Hidden, unchecked by default', 'squalomail-for-woocommerce');?><br/>
            </label>
        </div>


        <div class="box box-half margin-large">
            <label for="<?php echo $this->plugin_name; ?>-newsletter-checkbox-action">
                <h4><?php esc_html_e('Advanced Checkbox Settings', 'squalomail-for-woocommerce');?></h4>
                <p><?= sprintf(/* translators: %s - Woocommerce Actions documentation URL. */wp_kses( __( 'To change the location of the opt-in <br/>checkbox at checkout, input one of the <a href=%s target=_blank>available WooCommerce form actions</a>.', 'squalomail-for-woocommerce' ), array(  'a' => array( 'href' => array(), 'target'=> '_blank' ) ) ), esc_url( 'https://docs.woocommerce.com/wc-apidocs/hook-docs.html' ) ); ?></p>
            </label>
        </div>

        <div class="box box-half margin-large">
            <input type="text" id="<?php echo $this->plugin_name; ?>-newsletter-checkbox-action" name="<?php echo $this->plugin_name; ?>[squalomail_checkbox_action]" value="<?php echo isset($options['squalomail_checkbox_action']) ? $options['squalomail_checkbox_action'] : 'woocommerce_after_checkout_billing_form' ?>" />
            <p class="description"><?php esc_html_e('Enter a WooCommerce form action', 'squalomail-for-woocommerce'); ?></p>
        </div>

        <div class="box fieldset-header" >
            <h3><?php esc_html_e('Subscriber Settings', 'squalomail-for-woocommerce');?></h3>
        </div>

        <div class="box box-half" >
            <label for="<?php echo $this->plugin_name; ?>-user-tags">
                <h4><?php esc_html_e('Tags', 'squalomail-for-woocommerce');?></h4>
                <p><?= __( 'Add a comma-separated list of tags to apply to a subscriber in SqualoMail after a transaction occurs', 'squalomail-for-woocommerce' ); ?></p>
            </label>
        </div>

        <div class="box box-half" >
            <input type="text" id="<?php echo $this->plugin_name; ?>-user-tags" name="<?php echo $this->plugin_name; ?>[squalomail_user_tags]" value="<?php echo isset($options['squalomail_user_tags']) ? str_replace(',',', ',$options['squalomail_user_tags']) : '' ?>" />   
        </div>

        <div class="box fieldset-header" >
            <h3><?php esc_html_e('Product Settings', 'squalomail-for-woocommerce');?></h3>
        </div>


        <div class="box box-half">
            <label for="<?php echo $this->plugin_name; ?>[squalomail_product_image_key]">
                <h4><?php esc_html_e('Product Image Size', 'squalomail-for-woocommerce');?></h4>
                <p><?= __( 'Define the product image size used by abandoned carts, order notifications, and product recommendations.', 'squalomail-for-woocommerce' ); ?></p>
            </label>
        </div>

        <div class="box box-half" >
            <div class="squalomail-select-wrapper">
                <select name="<?php echo $this->plugin_name; ?>[squalomail_product_image_key]">
                    <?php
                    $enable_auto_subscribe = (array_key_exists('squalomail_product_image_key', $options) && !is_null($options['squalomail_product_image_key'])) ? $options['squalomail_product_image_key'] : 'medium';
                    foreach (squalomail_woocommerce_get_all_image_sizes_list() as $key => $value ) {
                        echo '<option value="' . esc_attr( $key ) . '" ' . selected($key == $enable_auto_subscribe, true, false ) . '>' . esc_html( $value ) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>
</fieldset>
