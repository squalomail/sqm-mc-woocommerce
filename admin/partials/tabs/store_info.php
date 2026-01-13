<?php

$handler = SqualoMail_WooCommerce_Admin::connect();

// if we don't have a valid api key we need to redirect back to the 'api_key' tab.
if (!$handler->hasValidApiKey()) {
    wp_redirect('admin.php?page=squalomail-woocommerce&tab=api_key&error_notice=missing_api_key');
}

?>
<fieldset class="">  
    <input type="hidden" name="squalomail_active_settings_tab" value="store_info"/>
    <?php 
        $current_currency = isset($options['store_currency_code']) ? $options['store_currency_code'] : get_woocommerce_currency();
        $current_currency_data = SqualoMail_WooCommerce_CurrencyCodes::getCurrency($current_currency);
    ?>
    <input type="hidden" value="<?php echo isset($current_currency_data) ? $current_currency . ' | ' .  $current_currency_data['name']: $current_currency ?>" disabled/>
    <input type="hidden" value="<?php echo squalomail_get_timezone(true)?>" disabled/>
  
    <legend class="screen-reader-text">
        <span><?php esc_html_e('Store Settings', 'squalomail-for-woocommerce');?></span>
    </legend>
    <div class="box box-half" >
        <label for="<?php echo $this->plugin_name; ?>-store-name-label">
            <span> <?php esc_html_e('Name', 'squalomail-for-woocommerce'); ?></span>
            <span class="required-field-mark">*</span>
        </label>
        <input required type="text" id="<?php echo $this->plugin_name; ?>-store-name-label" name="<?php echo $this->plugin_name; ?>[store_name]" value="<?php echo isset($options['store_name']) ? $options['store_name'] : get_option('blogname') ?>" />
    </div>

    <div class="box box-half" >
        <label for="<?php echo $this->plugin_name; ?>-admin-email-label">
                <span> <?php esc_html_e('Email', 'squalomail-for-woocommerce'); ?></span>
                <span class="required-field-mark">*</span>
        </label>
        <input required type="email" id="<?php echo $this->plugin_name; ?>-admin-email-label" name="<?php echo $this->plugin_name; ?>[admin_email]" value="<?php echo isset($options['admin_email']) ? $options['admin_email'] : get_option('admin_email') ?>" />
    </div>

    <div class="box box-half" >
        <label for="<?php echo $this->plugin_name; ?>-store-address-label">
                <span> <?php esc_html_e('Street address', 'squalomail-for-woocommerce'); ?></span>
                <span class="required-field-mark">*</span>
        </label>
        <input required type="text" id="<?php echo $this->plugin_name; ?>-store-address-label" name="<?php echo $this->plugin_name; ?>[store_street]" value="<?php echo isset($options['store_street']) ? $options['store_street'] : WC()->countries->get_base_address(); ?>" />
    </div>

    <div class="box box-half" >
        <label for="<?php echo $this->plugin_name; ?>-store-city-label">
                <span> <?php esc_html_e('City', 'squalomail-for-woocommerce'); ?></span>
                <span class="required-field-mark">*</span>
        </label>
        <input required type="text" id="<?php echo $this->plugin_name; ?>-store-city-label" name="<?php echo $this->plugin_name; ?>[store_city]" value="<?php echo isset($options['store_city']) ? $options['store_city'] : WC()->countries->get_base_city(); ?>" />
    </div>

    <div class="box box-half" >
        <label for="<?php echo $this->plugin_name; ?>-store-state-label">
                <span> <?php esc_html_e('State', 'squalomail-for-woocommerce'); ?></span>
                <span class="required-field-mark">*</span>
        </label>
        <input required type="text" id="<?php echo $this->plugin_name; ?>-store-state-label" name="<?php echo $this->plugin_name; ?>[store_state]" value="<?php echo isset($options['store_state']) ? $options['store_state'] : WC()->countries->get_base_state(); ?>" />
    </div>

    <div class="box box-half" >
        <label for="<?php echo $this->plugin_name; ?>-store-postal-code-label">
                <span> <?php esc_html_e('Postal Code', 'squalomail-for-woocommerce'); ?></span>
                <span class="required-field-mark">*</span>
        </label>
        <input required type="text" id="<?php echo $this->plugin_name; ?>-store-postal-code-label" name="<?php echo $this->plugin_name; ?>[store_postal_code]" value="<?php echo isset($options['store_postal_code']) ? $options['store_postal_code'] : WC()->countries->get_base_postcode(); ?>" />
    </div>

    <div class="box box-half" >
        <label for="<?php echo $this->plugin_name; ?>-store-country-label">
                <span> <?php esc_html_e('Country', 'squalomail-for-woocommerce'); ?></span>
                <span class="required-field-mark">*</span>
        </label>
        
        <?php global $woocommerce;
            $countries_obj   = new WC_Countries();
            $countries   = $countries_obj->__get('countries');
        ?>

        <div class="squalomail-select-wrapper">
            <?php 
            woocommerce_form_field($this->plugin_name.'[store_country]', array(
                'type'          => 'select',
                'class'         => array( 'chzn-drop' ),
                'placeholder'   => __('Select a Country'),
                'options'       => $countries,
                'required'      => true
                ),
                isset($options['store_country']) ? $options['store_country'] : WC()->countries->get_base_country()
            );
            
            ?>
        </div>
    </div>

    <div class="box box-half" >
        <label for="<?php echo $this->plugin_name; ?>-store-phone-label">
                <span> <?php esc_html_e('Phone Number', 'squalomail-for-woocommerce'); ?></span>
                <span class="required-field-mark">*</span>
        </label>
        <input required type="text" id="<?php echo $this->plugin_name; ?>-store-phone-label" name="<?php echo $this->plugin_name; ?>[store_phone]" value="<?php echo isset($options['store_phone']) ? $options['store_phone'] : '' ?>" />
    </div>

    <div class="box fieldset-header" >
        <h3 style="padding-top: 1em;"><?= __('Locale Settings', 'squalomail-for-woocommerce');?></h3>   
    </div>

    <div class="box" >
        <p>
            <?php

                echo sprintf(
                    /* translators: %1$s - The Currency name and format (ex: USD | US Dollar) %2$s - Timezone name or offset (ex: America/New_York or UTC-4:00) %3$s and %5$s- <a> tag open %4$s - </a> tag close*/
                    __('We\'ve detected that your WooCommerce store\'s currency is <b>%1$s</b> (%3$schange%4$s), and the WordPress timezone is <b>%2$s</b> (%5$schange%4$s).', 'squalomail-for-woocommerce'),
                    isset($current_currency_data) ? $current_currency . ' | ' .  $current_currency_data['name']: $current_currency,
                    squalomail_get_timezone(true),
                    '<a href="' . admin_url( 'admin.php?page=wc-settings#woocommerce_currency') .'" title="'.__( 'General Settings' ).'">',
                    '</a>',
                    '<a href="' . admin_url( 'options-general.php#timezone_string') .'" title="'.__( 'WooCommerce Settings' ).'">'
                );
            ?>
        </p>
    </div>

    <div class="box box-half" >
        
        <label for="<?php echo $this->plugin_name; ?>-store-locale-label">
            <span><?php esc_html_e('Locale', 'squalomail-for-woocommerce'); ?></span>
            <span class="required-field-mark">*</span>
            <p><?= __('Please apply your locale settings. If you\'re unsure about these, use the defaults.', 'squalomail-for-woocommerce');?></p>
        </label>
        
    </div>
    <div class="box box-half" >
        <div class="squalomail-select-wrapper">
            <select name="<?php echo $this->plugin_name; ?>[store_locale]" required>
                <option disabled selected value="<?= __('','squalomail-for-woocommerce')?>"><?= __("Select store's locale",'squalomail-for-woocommerce')?></option>
                <?php 
                $selected_locale = isset($options['store_locale']) && !empty($options['store_locale']) ? $options['store_locale'] : get_locale(); ?>
                <?php foreach(SqualoMail_Api_Locales::all() as $locale_key => $local_value) : ?>
                    <option value="<?php echo esc_attr( $locale_key ) . '" ' . selected($locale_key === $selected_locale, true, false ); ?>"> <?php esc_html_e( $local_value ) ?> </option>;
                <?php endforeach;?>
            </select>
        </div>
        
    </div>

    <div class="box" >
        
    </div>
    <?php 
        // Only admins should see squalomail_permission_cap radio buttons
        if (current_user_can('manage_options')) : ?>
        <div class="box fieldset-header" >
            <h3 style="padding-top: 1em;"><?= __('Permission Settings', 'squalomail-for-woocommerce');?></h3>   
        </div>
        <div class="optional-settings-content">
            <div class="box box-half margin-large">
                <label>
                    <h4><?php esc_html_e('Plugin Permission Level', 'squalomail-for-woocommerce');?></h4>
                    <p><?php _e('Select the minimum permission capability to manage Squalo for Woocommerce options', 'squalomail-for-woocommerce');?> </p>
                </label>
            </div>

            <div class="box box-half margin-large">
                <?php $checkbox_default_settings = (array_key_exists('squalomail_permission_cap', $options) && !is_null($options['squalomail_permission_cap'])) ? $options['squalomail_permission_cap'] : 'manage_options'; ?>
                <label class="radio-label">
                    <input type="radio" name="<?php echo $this->plugin_name; ?>[squalomail_permission_cap]" value="manage_options"<?php if($checkbox_default_settings === 'manage_options') echo ' checked="checked" '; ?>><?php esc_html_e('Administrators Only', 'squalomail-for-woocommerce');?><br>
                </label>
                <label class="radio-label">
                    <input type="radio" name="<?php echo $this->plugin_name; ?>[squalomail_permission_cap]" value="manage_woocommerce"<?php if($checkbox_default_settings === 'manage_woocommerce') echo ' checked="checked" '; ?>><?php esc_html_e('Shop Managers and Administrators', 'squalomail-for-woocommerce');?><br/>
                </label>
            </div>
        </div>
    <?php endif; ?>
</fieldset>