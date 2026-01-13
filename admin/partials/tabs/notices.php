<?php if(isset($_GET['error_notice'])): ?>
<div class="error notice is-dismissable">
    <?php
        switch($_GET['error_notice']) {
            case 'missing_api_key':
                esc_html_e('Squalo says: You must enter in a valid API key.', 'squalomail-for-woocommerce');
            break;
            case 'missing_list':
                esc_html_e('Squalo says: You must select a marketing audience.', 'squalomail-for-woocommerce');
                break;
            case 'missing_store':
                esc_html_e('Squalo says: Sorry you must set up your store before you proceed!', 'squalomail-for-woocommerce');
                break;
            case 'not_ready_for_sync':
                esc_html_e('Squalo says: You are not fully ready to run the Store Sync, please verify your settings before proceeding.', 'squalomail-for-woocommerce');
                break;
            default:

        }
    ?>
</div>
<?php endif; ?>

<?php if (isset($_GET['success_notice'])): ?>
    <div class="success notice is-dismissable">
        <?php
        switch($_GET['error_notice']) {
            case 're-sync-started':
                esc_html_e('Squalo says: Your re-sync has been started!', 'squalomail-for-woocommerce');
                break;
            default:
        }
        ?>
    </div>
<?php endif; ?>

