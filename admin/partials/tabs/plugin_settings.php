<input type="hidden" name="squalomail_active_settings_tab" value="plugin_settings"/>

<?php
$store_id = squalomail_get_store_id();

$opt = get_option('squalomail-woocommerce-comm.opt');
$admin_email = squalomail_get_option('admin_email', get_option('admin_email'));
$comm_enabled = $opt != null ? $opt : '0';
?>
<fieldset>
    <legend class="screen-reader-text">
        <span><?php esc_html_e('Plugin Settings', 'squalomail-for-woocommerce');?></span>
	</legend>

	<div class="box ">
		<label for="<?php echo $this->plugin_name; ?>-newsletter-checkbox-label">
			<h4><?php esc_html_e('Communication', 'squalomail-for-woocommerce'); ?></h4>
			<p>
				<?php 
				
				echo sprintf(
					/* translators: Placeholders %1$s - admin email address */
					__('Occasionally we may send you information about how-to\'s, updates, and other news to the store\'s admin email address. Choose whether or not you want to receive these messages at %1$s ', 'squalomail-for-woocommerce'),
					$admin_email
				);?>
			</p>
		</label>
		<br/>
		<fieldset>    
			<p id="sqm-comm-wrapper">
				<label class="el-switch el-checkbox-green">
					<input id="comm_box_switch" type="checkbox" name="switch" <?php if($comm_enabled === '1') echo ' checked="checked" '; ?> value="1">
					<span><?= __('Opt-in to our newsletter', 'squalomail-for-woocommerce'); ?></span>
					<br/>
					<span class="sqm-comm-save" id="sqm-comm-save">Saved</span>
				</label>
				
			</p>
		</fieldset>
	</div>

	<div class="box"></div>
	<div class="box">
		<label for="<?php echo $this->plugin_name; ?>-newsletter-checkbox-label">
			<h4><?php esc_html_e('Disconnect Store', 'squalomail-for-woocommerce'); ?></h4>
			<p>
				<?= 
				sprintf(
					__('Disconnect your store from Squalo. This action will remove all entries from the database but you will be able to reconnect anytime.', 'squalomail-for-woocommerce'),
					$admin_email
				);?>
			</p>
		</label>
		<p>
			<?php wp_nonce_field( '_disconnect-nonce-'.$store_id, '_disconnect-nonce' ); ?>

			<a id="squalomail_woocommerce_disconnect" class="sqm-woocommerce-disconnect-button button button-default tab-content-submit">
				<?php esc_html_e('Disconnect Store', 'squalomail-for-woocommerce');?>
			</a>
		</p>
	</div>

	<div class="box box-half comm_box_wrapper">
		
	</div>
</fieldset>