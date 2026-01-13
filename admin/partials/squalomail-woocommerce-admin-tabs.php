<?php
// Grab plugin admin object
$handler = SqualoMail_WooCommerce_Admin::connect();

// Grab all options for this particular tab we're viewing.
$options = get_option($this->plugin_name, array());

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : (isset($options['active_tab']) ? $options['active_tab'] : 'api_key');
$sqm_configured = squalomail_is_configured();

if (!$sqm_configured) {
    if ($active_tab == 'sync' || $active_tab == 'logs' ) isset($options['active_tab']) ? $options['active_tab'] : 'api_key';
}

$is_squalomail_post = isset($_POST['squalomail_woocommerce_settings_hidden']) && $_POST['squalomail_woocommerce_settings_hidden'] === 'Y';

$show_sync_tab = isset($_GET['resync']) ? $_GET['resync'] === '1' : false;

// if we have a transient set to start the sync on this page view, initiate it now that the values have been saved.
if ($sqm_configured && !$show_sync_tab && (bool) get_site_transient('squalomail_woocommerce_start_sync', false)) {
    $show_sync_tab = true;
    $active_tab = 'sync';
}

$show_newsletter_settings = true;
$has_valid_api_key = false;
$allow_new_list = true;
$only_one_list = false;
$show_wizard = true;
$clicked_sync_button = $sqm_configured && $is_squalomail_post && $active_tab == 'sync';
$has_api_error = isset($options['api_ping_error']) && !empty($options['api_ping_error']) ? $options['api_ping_error'] : null;

if (isset($options['squalomail_api_key'])) {
    try {
        if ($handler->hasValidApiKey(null, true)) {
            $has_valid_api_key = true;

            // if we don't have a valid api key we need to redirect back to the 'api_key' tab.
            if (($squalomail_lists = $handler->getSqualoMailLists()) && is_array($squalomail_lists)) {
                $show_newsletter_settings = true;
                $allow_new_list = false;
                $only_one_list = count($squalomail_lists) === 1;

            }

            // only display this button if the data is not syncing and we have a valid api key
            if ((bool) $this->getData('sync.started_at', false)) {
                $show_sync_tab = true;
            }

            //display wizard if not all steps are complete
            if ($show_sync_tab && $this->getData('validation.store_info', false) && $this->getData('validation.newsletter_settings', false)) {
                $show_wizard = false;        
            }
                
        }
    } catch (\Exception $e) {
        $has_api_error = $e->getMessage().' on '.$e->getLine().' in '.$e->getFile();
    }
}
else {
    $active_tab = 'api_key';
}

?>

<div class="sqm-woocommerce-settings">
    <form id="squalomail_woocommerce_options" method="post" name="cleanup_options" action="options.php">
        <?php if($show_wizard): ?>
            <div class="sqm-woocommerce-settings-header-wrapper wiz-header">
                <div class="sqm-woocommerce-settings-header">

                    <svg width="140"  viewBox="0 0 7424 1701" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_282_566)">
                            <mask id="mask0_282_566" style="mask-type:luminance" maskUnits="userSpaceOnUse" x="0" y="0" width="7424" height="1701">
                                <path d="M7423.63 0H0V1701H7423.63V0Z" fill="white"/>
                            </mask>
                            <g mask="url(#mask0_282_566)">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M2212.3 1209.05C2217.1 1205.65 2250.3 1181.62 2246.3 1174.41C2201.6 1095.87 2013.6 770.85 1787 559.02C1621.6 404.67 1438.4 280.07 1242.7 189.25C997.5 75.39 732 14.0999 454 7.16992L164.1 0L391.3 179.86C391.33 179.885 391.412 179.952 391.544 180.06C398.334 185.605 537.727 299.438 636.899 467.08C754.799 666.39 769.8 857.65 681.2 1035.71L662.899 1072.41C681.699 1062.56 702 1054.73 724.2 1049.14C730 1047.8 736 1046.46 742.1 1045.56C748.1 1044.22 754.399 1043.32 760.899 1042.65C773.599 1041.08 786.8 1040.19 800.5 1040.19C807.4 1040.19 814.1 1040.41 820.8 1040.86C826.2 1041.3 831.6 1041.76 836.7 1042.21C847.9 1043.54 858.799 1045.56 869.399 1047.8C873.143 1047.8 876.752 1048 880.407 1048.2C882.219 1048.29 884.043 1048.39 885.899 1048.47C967.099 830.59 938.7 595.03 802 365.74C773.4 317.43 740.3 270.23 703.6 224.14C1056.4 281.19 1374.9 440.24 1651.8 697.94C1831.53 865.238 2003.01 1146.57 2057.45 1235.88L2059.8 1239.73C2063.77 1239.68 2067.74 1239.64 2071.71 1239.6C2120.92 1239.15 2170.29 1238.69 2212.3 1209.05ZM280.833 1271.77L280.839 1271.77L280.84 1271.77C318.703 1250.56 356.536 1229.37 396.8 1214.95C605.1 1140.36 821.5 1129.88 1040.1 1146.8C1197.13 1158.96 1349.56 1194.88 1502.01 1230.8C1578.77 1248.89 1655.54 1266.97 1732.9 1282.03C1833.3 1301.57 1935.2 1318.34 2037 1324.32C2264.9 1337.73 2474.3 1287.25 2645 1121.43C2609.7 1224.46 2539.7 1298.8 2450.8 1355.62C2276 1467.31 2080.4 1504.95 1878.8 1482.55C1742.58 1467.41 1607.52 1441.17 1472.52 1414.94C1418.33 1404.41 1364.15 1393.89 1309.9 1384.08C1243.68 1372.11 1177.57 1359.39 1111.45 1346.67L1111.42 1346.67L1111.41 1346.67C974.763 1320.38 838.109 1294.1 700.5 1274.38C544.1 1251.98 386.5 1262.38 234.1 1315.92L233.84 1315.26L233.833 1315.24C231.922 1310.36 230.007 1305.46 228 1300.56C245.78 1291.41 263.31 1281.59 280.833 1271.77ZM41.5671 1524.72L41.5697 1524.72C70.3071 1500.96 97.4463 1478.53 128.6 1466.61C200.4 1439.12 277 1416.93 353.3 1409.89C583.8 1388.6 805.799 1441.83 1025.9 1503.24C1198.4 1551.38 1372.2 1592.8 1553.2 1589.75C1555.57 1589.71 1557.99 1590.73 1561.83 1592.35C1564.13 1593.33 1566.96 1594.53 1570.6 1595.84C1494.2 1657.07 1408.3 1678.83 1319.1 1692.22C1156.66 1716.57 999.056 1685.76 841.057 1654.88L841.056 1654.88C833.805 1653.46 826.553 1652.04 819.3 1650.63C803.895 1647.63 788.49 1644.62 773.083 1641.61C607.163 1609.21 441.068 1576.77 273.8 1553.57C216.65 1545.65 157.895 1549.25 94.399 1553.15C64.1238 1555.01 32.7707 1556.93 0 1557.7C14.5724 1547.03 28.2416 1535.74 41.5671 1524.72Z" fill="url(#paint0_linear_282_566)"/>
                                <mask id="mask1_282_566" style="mask-type:luminance" maskUnits="userSpaceOnUse" x="164" y="0" width="2083" height="1240">
                                    <path d="M2246.3 1174.41C2250.3 1181.62 2217.1 1205.65 2212.3 1209.05C2166.9 1241.08 2112.9 1239.03 2059.8 1239.73C2008.1 1154.94 1834.2 867.72 1651.8 697.94C1374.9 440.24 1056.4 281.19 703.6 224.14C740.3 270.23 773.4 317.43 802 365.74C938.7 595.03 967.099 830.59 885.899 1048.47C880.299 1048.24 874.999 1047.8 869.399 1047.8C858.799 1045.56 847.9 1043.54 836.7 1042.21C831.6 1041.76 826.2 1041.3 820.8 1040.86C814.1 1040.41 807.4 1040.19 800.5 1040.19C786.8 1040.19 773.599 1041.08 760.899 1042.65C754.399 1043.32 748.1 1044.22 742.1 1045.56C736 1046.46 730 1047.8 724.2 1049.14C702 1054.73 681.699 1062.56 662.899 1072.41L681.2 1035.71C769.8 857.65 754.799 666.39 636.899 467.08C535.799 296.18 392.9 181.2 391.3 179.86L164.1 0L454 7.16992C732 14.0999 997.5 75.39 1242.7 189.25C1438.4 280.07 1621.6 404.67 1787 559.02C2013.6 770.85 2201.6 1095.87 2246.3 1174.41Z" fill="white"/>
                                </mask>
                                <g mask="url(#mask1_282_566)">
                                    <path d="M2246.3 1174.41C2250.3 1181.62 2217.1 1205.65 2212.3 1209.05C2166.9 1241.08 2112.9 1239.03 2059.8 1239.73C2008.1 1154.94 1834.2 867.72 1651.8 697.94C1374.9 440.24 1056.4 281.19 703.6 224.14C740.3 270.23 773.4 317.43 802 365.74C938.7 595.03 967.099 830.59 885.899 1048.47C880.299 1048.24 874.999 1047.8 869.399 1047.8C858.799 1045.56 847.9 1043.54 836.7 1042.21C831.6 1041.76 826.2 1041.3 820.8 1040.86C814.1 1040.41 807.4 1040.19 800.5 1040.19C786.8 1040.19 773.599 1041.08 760.899 1042.65C754.399 1043.32 748.1 1044.22 742.1 1045.56C736 1046.46 730 1047.8 724.2 1049.14C702 1054.73 681.699 1062.56 662.899 1072.41L681.2 1035.71C769.8 857.65 754.799 666.39 636.899 467.08C535.799 296.18 392.9 181.2 391.3 179.86L164.1 0L454 7.16992C732 14.0999 997.5 75.39 1242.7 189.25C1438.4 280.07 1621.6 404.67 1787 559.02C2013.6 770.85 2201.6 1095.87 2246.3 1174.41Z" fill="url(#paint1_linear_282_566)"/>
                                </g>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3291.2 413.209C3397.9 413.209 3490.6 466.57 3524.3 522.76L3671.8 446.91C3597.4 295.23 3438.7 251.68 3285.6 251.68C3104.4 253.09 2904.9 335.97 2904.9 538.22C2904.9 758.7 3090.3 812.099 3291.2 835.969C3421.8 850.009 3518.7 887.92 3518.7 980.65C3518.7 1087.36 3409.2 1128.11 3292.6 1128.11C3173.2 1128.11 3059.4 1080.35 3015.9 972.2L2861.4 1052.24C2934.4 1232.03 3088.9 1293.85 3289.8 1293.85C3508.9 1293.85 3702.7 1199.74 3702.7 980.65C3702.7 746.08 3511.7 692.69 3306.6 667.43C3188.7 653.36 3087.5 629.52 3087.5 543.81C3087.5 470.79 3153.5 413.209 3291.2 413.209ZM4195.8 726.419C4319.4 726.419 4392.4 814.9 4392.4 923.03C4392.4 1031.2 4312.4 1119.67 4195.8 1119.67C4079.2 1119.67 3999.1 1031.2 3999.1 923.03C3999.1 814.9 4072.2 726.419 4195.8 726.419ZM4407.9 1187.1V1557.89H4579.2V576.12H4419.1L4407.9 671.649C4354.5 594.379 4261.8 560.68 4183.1 560.68C3969.6 560.68 3827.8 719.41 3827.8 923.03C3827.8 1125.3 3955.6 1285.41 4177.5 1285.41C4250.5 1285.41 4358.7 1262.92 4407.9 1187.1ZM4891.1 576.12V938.479C4891.1 1043.85 4948.7 1123.89 5058.2 1123.89C5163.6 1123.89 5235.2 1035.42 5235.2 930.08V576.12H5405.1V1269.96H5252L5240.8 1175.87C5169.2 1246.09 5103.2 1279.78 5006.2 1279.78C4840.5 1279.78 4719.7 1154.78 4719.7 939.9V576.12H4891.1ZM5895.4 1132.31C5781.6 1132.31 5690.3 1053.66 5690.3 921.64C5690.3 789.63 5781.6 712.36 5895.4 712.36C6165.1 712.36 6165.1 1132.31 5895.4 1132.31ZM6270.4 576.12H6106.1L6100.4 671.65C6061.1 602.8 5972.6 559.26 5879.9 559.26C5677.7 557.87 5519 682.87 5519 921.64C5519 1164.63 5670.7 1291.02 5875.7 1289.64C5953 1288.22 6061.1 1248.89 6100.4 1167.43L6108.9 1268.55H6270.4V576.12ZM6585.1 286.79V1268.55H6415.1V286.79H6585.1ZM7062.6 1126.69C6937.6 1126.69 6874.4 1028.37 6874.4 923.03C6874.4 819.1 6939 717.99 7062.6 717.99C7177.8 717.99 7250.8 819.1 7250.8 923.03C7250.8 1028.37 7187.6 1126.69 7062.6 1126.69ZM7062.6 1284C7287.3 1284 7423.6 1122.47 7423.6 923.03C7423.6 725 7281.7 562.089 7061.2 562.089C6840.7 562.089 6703.1 725 6703.1 923.03C6703.1 1122.47 6837.9 1284 7062.6 1284Z" fill="url(#paint2_linear_282_566)"/>
                            </g>
                        </g>
                        <defs>
                            <linearGradient id="paint0_linear_282_566" x1="0" y1="850.495" x2="2645.08" y2="850.495" gradientUnits="userSpaceOnUse">
                                <stop offset="0.141936" stop-color="#1C2C84"/>
                                <stop offset="0.381152" stop-color="#853B9E"/>
                                <stop offset="0.589118" stop-color="#DF1729"/>
                                <stop offset="0.840936" stop-color="#F8770E"/>
                            </linearGradient>
                            <linearGradient id="paint1_linear_282_566" x1="164.1" y1="619.86" x2="2246.7" y2="619.86" gradientUnits="userSpaceOnUse">
                                <stop offset="0.141936" stop-color="#1C2C84"/>
                                <stop offset="0.381152" stop-color="#853B9E"/>
                                <stop offset="0.589118" stop-color="#DF1729"/>
                                <stop offset="0.840936" stop-color="#F8770E"/>
                            </linearGradient>
                            <linearGradient id="paint2_linear_282_566" x1="2861.4" y1="904.779" x2="7423.75" y2="904.779" gradientUnits="userSpaceOnUse">
                                <stop offset="0.141936" stop-color="#1C2C84"/>
                                <stop offset="0.381152" stop-color="#853B9E"/>
                                <stop offset="0.589118" stop-color="#DF1729"/>
                                <stop offset="0.840936" stop-color="#F8770E"/>
                            </linearGradient>
                            <clipPath id="clip0_282_566">
                                <rect width="7423.63" height="1701" fill="white"/>
                            </clipPath>
                        </defs>
                    </svg>


                    <div class="sqm-woocommerce-settings-subtitles">
                        <?php
                            $allowed_html = array(
                                'br' => array()
                            );
                        ?>
                        

                        <?php if ($active_tab == 'api_key' ) : ?>
                            <span class="sqm-woocommerce-header-steps">1 of 3 - Connect</span>
                            <span class="sqm-woocommerce-header-title"> <?php wp_kses(_e('Add Squalo for WooCommerce', 'squalomail-for-woocommerce'), $allowed_html);?> </span>
                            <span class="sqm-woocommerce-header-subtitle"> <?php wp_kses(_e('Build custom segments, send automations, and track purchase <br/>activity in Squalo. Login to authorize an account connection.', 'squalomail-for-woocommerce'), $allowed_html);?> </span>
                        <?php endif;?>
                
                        <?php if ($active_tab == 'store_info' && $has_valid_api_key) :?>
                            <span class="sqm-woocommerce-header-steps">2 of 3 - Store</span>    
                            <span class="sqm-woocommerce-header-title"> <?php wp_kses(_e('Add WooCommerce store settings', 'squalomail-for-woocommerce'), $allowed_html);?> </span>
                            <span class="sqm-woocommerce-header-subtitle"> <?php wp_kses(_e('Please provide a bit of information about your WooCommerce <br/> store and location.', 'squalomail-for-woocommerce'), $allowed_html);?> </span>                      
                        <?php endif;?>
                
                        <?php if ($active_tab == 'newsletter_settings' ) :?>
                            <span class="sqm-woocommerce-header-steps">3 of 3 - Audience</span>    
                            <span class="sqm-woocommerce-header-title"> <?php wp_kses(_e('Add WooCommerce audience settings', 'squalomail-for-woocommerce'), $allowed_html);?> </span>
                            <span class="sqm-woocommerce-header-subtitle">
                                <?php wp_kses(_e('Please provide a bit of information about your WooCommerce <br/> campaign and messaging settings.', 'squalomail-for-woocommerce'), $allowed_html);?> 
                                <?php if (!$only_one_list) wp_kses(_e('If you don’t have an audience, <br/> you can choose to create one', 'squalomail-for-woocommerce'), $allowed_html); ?>
                            </span>
                        <?php endif;?>    
                    </div>
                    <?php if ($active_tab == 'api_key' ): ?>
                        
                            <div class="box">
                                <?php if ($show_wizard) : ?>
                                    <input type="hidden" name="squalomail_woocommerce_wizard_on" value=1>
                                <?php endif; ?>
                                
                                <input type="hidden" name="squalomail_woocommerce_settings_hidden" value="Y">
                                
                                <?php
                                    if (!$clicked_sync_button) {
                                        settings_fields($this->plugin_name);
                                        do_settings_sections($this->plugin_name);
                                        include('tabs/notices.php');
                                    }
                                ?>
                            </div>
                        
                            <div class="connect-buttons">
                                <?php include_once 'tabs/api_key.php'; ?>
                            </div>
                    <?php endif; ?>
                    <div class="sqm-woocommerce-wizard-btn">
                        <?php if ($active_tab == 'store_info' && $has_valid_api_key) : ?>
                                <?php submit_button(__('Next step', 'squalomail-for-woocommerce'), 'primary tab-content-submit','squalomail_submit', TRUE); ?>
                                <a href="?page=squalomail-woocommerce&tab=api_key" class="button button-default back-step"><?= __('Back', 'squalomail-for-woocommerce') ?></a>
                        <?php endif; ?>

                        <?php if ($active_tab == 'newsletter_settings' && $has_valid_api_key) : ?>
                                <?php submit_button(__('Start sync', 'squalomail-for-woocommerce'), 'primary tab-content-submit','squalomail_submit', TRUE); ?>
                                <a href="?page=squalomail-woocommerce&tab=store_info" class="button button-default back-step"><?= __('Back', 'squalomail-for-woocommerce') ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="sqm-woocommerce-settings-header-wrapper">
                <div class="sqm-woocommerce-settings-header">
                    <svg xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:cc="http://creativecommons.org/ns#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd" xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape" width="46" height="49" viewBox="0 0 80 82" version="1.1" id="svg8" sodipodi:docname="squalomail-shark-basic.svg" inkscape:version="0.92.3 (2405546, 2018-03-11)" class="squalomail-logo">
                        <defs id="defs2">
                            <clipPath clipPathUnits="userSpaceOnUse" id="clipPath3987">
                                <rect id="rect3989" width="81.340477" height="80.962494" x="67.204163" y="99.998817" style="stroke-width:0.62043113"></rect>
                            </clipPath>
                        </defs>
                        <sodipodi:namedview id="base" pagecolor="#ffffff" bordercolor="#666666" borderopacity="1.0" inkscape:pageopacity="0.0" inkscape:pageshadow="2" inkscape:zoom="0.98994949" inkscape:cx="113.8152" inkscape:cy="198.71572" inkscape:document-units="mm" inkscape:current-layer="layer1" showgrid="false" inkscape:window-width="1853" inkscape:window-height="1025" inkscape:window-x="67" inkscape:window-y="27" inkscape:window-maximized="1"></sodipodi:namedview>
                        <metadata id="metadata5">
                            <rdf:rdf>
                                <cc:work rdf:about="">
                                    <dc:format>image/svg+xml</dc:format>
                                    <dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage"></dc:type>
                                    <dc:title></dc:title>
                                </cc:work>
                            </rdf:rdf>
                        </metadata>
                        <g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer1" transform="translate(0,-215)">
                            <image y="99.99881" x="67.204163" id="image3775" xlink:href="data:image/png;base64,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" style="image-rendering:optimizeQuality" preserveAspectRatio="none" height="82.550003" width="77.258331" clip-path="url(#clipPath3987)" transform="translate(-66.706675,115.48763)"></image>
                        </g>
                    </svg>
                    
                    <p class="sqm-woocommerce-settings-subtitles">
                        <?php
                        
                        $allowed_html = array(
                            'br' => array()
                        );
                        
                        if ($active_tab == 'api_key' ) {
                            wp_kses(_e('Add Squalo for WooCommerce to build custom segments, send automations, and track purchase activity in Squalo', 'squalomail-for-woocommerce'), $allowed_html);
                        }
                
                        if ($active_tab == 'store_info' && $has_valid_api_key) {
                            if ($show_sync_tab) {
                                wp_kses(_e('WooCommerce store and location', 'squalomail-for-woocommerce'), $allowed_html);
                            }
                            else wp_kses(_e('Please provide a bit of information about your WooCommerce store', 'squalomail-for-woocommerce'), $allowed_html);
                        }
                
                        if ($active_tab == 'newsletter_settings' ) {
                            if ($show_sync_tab) {
                                wp_kses(_e('Campaign and messaging settings', 'squalomail-for-woocommerce'), $allowed_html);
                            }
                            else {
                                if ($only_one_list) {
                                    wp_kses(_e('Please apply your audience settings.', 'squalomail-for-woocommerce'), $allowed_html);
                                }
                                else {
                                    wp_kses(_e('Please apply your audience settings. ', 'squalomail-for-woocommerce'), $allowed_html);
                                    wp_kses(_e('If you don’t have an audience, you can choose to create one', 'squalomail-for-woocommerce'), $allowed_html);    
                                }
                            }
                        }
                        if ($active_tab == 'sync' && $show_sync_tab) {
                            if (squalomail_is_done_syncing()) {
                                wp_kses(_e('Success! You are connected to Squalo', 'squalomail-for-woocommerce'), $allowed_html);
                            }
                            else {
                                wp_kses(_e('Your WooCommerce store is syncing to Squalo', 'squalomail-for-woocommerce'), $allowed_html);
                            }
                        }
                
                        if ($active_tab == 'logs' && $show_sync_tab) {
                            wp_kses(_e('Log events from the Squalo plugin', 'squalomail-for-woocommerce'), $allowed_html);
                        }

                        if ($active_tab == 'plugin_settings' && $show_sync_tab) {
                            wp_kses(_e('Connection settings', 'squalomail-for-woocommerce'), $allowed_html);
                        }
                        ?>
                    </p>
                    <div class="nav-tab-wrapper">
                        <?php if($has_valid_api_key): ?>
                            <?php if ($active_tab == 'api_key'): ?>
                                <a href="?page=squalomail-woocommerce&tab=api_key" class="nav-tab <?php echo $active_tab == 'api_key' ? 'nav-tab-active' : ''; ?>"><?= esc_html_e('Connect', 'squalomail-for-woocommerce');?></a>
                            <?php endif ;?>
                            <a href="?page=squalomail-woocommerce&tab=sync" class="nav-tab <?php echo $active_tab == 'sync' ? 'nav-tab-active' : ''; ?>"><?= esc_html_e('Overview', 'squalomail-for-woocommerce');?></a>
                            <a href="?page=squalomail-woocommerce&tab=store_info" class="nav-tab <?php echo $active_tab == 'store_info' ? 'nav-tab-active' : ''; ?>"><?= esc_html_e('Store', 'squalomail-for-woocommerce');?></a>
                            <?php if ($handler->hasValidStoreInfo()) : ?>
                            
                                    <a href="?page=squalomail-woocommerce&tab=newsletter_settings" class="nav-tab <?php echo $active_tab == 'newsletter_settings' ? 'nav-tab-active' : ''; ?>"><?= esc_html_e('Audience', 'squalomail-for-woocommerce');?></a>
                            <?php endif;?>
                            <a href="?page=squalomail-woocommerce&tab=logs" class="nav-tab <?php echo $active_tab == 'logs' ? 'nav-tab-active' : ''; ?>"><?= esc_html_e('Logs', 'squalomail-for-woocommerce');?></a>
                            <a href="?page=squalomail-woocommerce&tab=plugin_settings" class="nav-tab <?php echo $active_tab == 'plugin_settings' ? 'nav-tab-active' : ''; ?>"><?= esc_html_e('Settings', 'squalomail-for-woocommerce');?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="notices-content-wrapper <?= $active_tab == 'sync' ? 'sync-notices' : '' ?>">
        <?php
            $settings_errors = get_settings_errors();
            if (!$show_wizard || ($show_wizard && isset($settings_errors[0]) && $settings_errors[0]['type'] != 'success' )) {
                echo squalomail_settings_errors();
            }
        ?>
        </div>
        <?php if ($active_tab != 'sync'): ?>
        <div class="tab-content-wrapper">
        <?php endif; ?>
                <?php if (!defined('PHP_VERSION_ID') || (PHP_VERSION_ID < 70000)): ?>
                    <div data-dismissible="notice-php-version" class="error notice notice-error">
                        <p><?php esc_html_e('Squalo says: Please upgrade your PHP version to a minimum of 7.0', 'squalomail-for-woocommerce'); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($has_api_error)): ?>
                    <div data-dismissible="notice-api-error" class="error notice notice-error is-dismissible">
                        <p><?php esc_html_e("Squalo says: API Request Error - ".$has_api_error, 'squalomail-for-woocommerce'); ?></p>
                    </div>
                <?php endif; ?>
                <div class="box">
                    <?php if ($show_wizard) : ?>
                        <input type="hidden" name="squalomail_woocommerce_wizard_on" value=1>
                    <?php endif; ?>
                    
                    <input type="hidden" name="squalomail_woocommerce_settings_hidden" value="Y">
                
                    <?php
                        if (!$clicked_sync_button) {
                            settings_fields($this->plugin_name);
                            do_settings_sections($this->plugin_name);
                            include('tabs/notices.php');
                        }
                    ?>
                </div>
                

                <input type="hidden" name="<?php echo $this->plugin_name; ?>[squalomail_active_tab]" value="<?php echo esc_attr($active_tab); ?>"/>
                
                <?php if ($active_tab == 'api_key'): ?>
                    <?php //include_once 'tabs/api_key_content.php'; ?>
                <?php endif; ?>

                <?php if ($active_tab == 'store_info' && $has_valid_api_key): ?>
                    <?php include_once 'tabs/store_info.php'; ?>
                <?php endif; ?>

                <?php if ($active_tab == 'newsletter_settings' ): ?>
                    <?php include_once 'tabs/newsletter_settings.php'; ?>
                <?php endif; ?>

                <?php if ($active_tab == 'sync' && $show_sync_tab): ?>
                    <?php include_once 'tabs/store_sync.php'; ?>
                <?php endif; ?>

                <?php if ($active_tab == 'logs' && $show_sync_tab): ?>
                    <?php include_once 'tabs/logs.php'; ?>
                <?php endif; ?>

                <?php if ($active_tab == 'plugin_settings' && $show_sync_tab): ?>
                    <?php include_once 'tabs/plugin_settings.php'; ?>
                <?php endif; ?>
                <?php if (squalomail_is_configured()) : ?>
                    <div class="box"> 
                        <?php 
                            if ($active_tab !== 'api_key' && $active_tab !== 'sync' && $active_tab !== 'logs' && $active_tab != 'plugin_settings') {
                                submit_button(__('Save all changes'), 'primary tab-content-submit','squalomail_submit', TRUE);
                            }
                        ?>
                    </div>
                <?php endif; ?>
        <?php if ($active_tab != 'sync'): ?>
        </div>
        <?php endif; ?>
    </form>
</div><!-- /.wrap -->
