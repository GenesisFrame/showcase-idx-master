<?php

// SHORTCODE HANDLERS
// curried shortcode generators
function showcaseidx_widget_230() { return showcaseidx_generate_widget('widgets230'); }
function showcaseidx_widget_465() { return showcaseidx_generate_widget('widgets465'); }
function showcaseidx_widget_700() { return showcaseidx_generate_widget('widgets700'); }
function showcaseidx_widget_930() { return showcaseidx_generate_widget('widgets930'); }
function showcaseidx_generate_widget($type)
{
    $config = showcaseidx_generate_config();
    $cdn    = "cdn.showcaseidx.com";

    $widgetUrl = "http://$cdn/{$type}";
    $widget = showcaseidx_cachable_fetch($widgetUrl);

    $searchHostPage = showcaseidx_base_url() . '/';
    $widget = str_replace('action_url', $searchHostPage, $widget);
    return <<<EOT
        {$config}
        <link href="http://$cdn/css/screen.css" media="screen, projection" rel="stylesheet" type="text/css" />
        {$widget}
EOT;
}

function showcaseidx_show_app() {
    $seoPlaceholder = '<a href="' . showcaseidx_base_url() . '/all">View all listings</a>';
    return showcaseidx_generate_app($seoPlaceholder);
}

function showcaseidx_show_hotsheet($scParams) {
    $shortcodeAttrs = shortcode_atts(array(
        'type' => 'custom',                 // custom, agent, office
        'name' => '',                       // name of hotsheet; only referenced for type=custom
        'hide_map' => 'false',
        'hide_search' => 'false',
        'recent' => '',
    ), $scParams);
    $searchConfigJSON = "{ 'hotsheet': { 'type': '{$shortcodeAttrs['type']}', 
                                         'name': '{$shortcodeAttrs['name']}',
                                         'hide_map': {$shortcodeAttrs['hide_map']},
                                         'hide_search': {$shortcodeAttrs['hide_search']},
                                         'recent': '{$shortcodeAttrs['recent']}'
                                          } }"; // WP might not have json_encode
    return showcaseidx_generate_app("Hotsheet: {$name}", NULL, $searchConfigJSON);
}

/*************** HELPER FUNCTIONS FOR SHORTCODE GENERATORS **********************/
function showcaseidx_generate_app($seoPlaceholder = NULL, $defaultAppUrl = NULL, $customSearchConfig = NULL) {
    if ($customSearchConfig === NULL)
    {
        $customSearchConfig = showcaseidx_get_custom_widget_config();
    }
    $config = showcaseidx_generate_config($customSearchConfig);
    $defaultAppUrl = $defaultAppUrl ? showcaseidx_generate_default_app_url($defaultAppUrl) : NULL;
    $widget = showcaseidx_cachable_fetch("http://cdn.showcaseidx.com/wordpress_noscript");
    return <<<EOT
        {$config}
        {$defaultAppUrl}
        <div class="mydx2-hide">
            {$seoPlaceholder}
        </div>
        {$widget}
EOT;
}

function showcaseidx_generate_config($customSearchConfig = null) {
    $WEBSITE_ID = get_option('showcaseidx_api_key');

    if ($customSearchConfig === NULL)
    {
        $customSearchConfig = 'null';
    }

    return <<<EOT
<script type="text/javascript">
if (!SHOWCASE_CONF) {
    var SHOWCASE_CONF = {
        WEBSITE_ID: "{$WEBSITE_ID}"
    };
}
if ($customSearchConfig) {
    SHOWCASE_CONF.SEARCH_CONF = $customSearchConfig;
}
</script>
EOT;
}

// grabs & sanitizes the "customSearchConfig" from the form data posted by widgets (see showcaseidx_generate_config)
function showcaseidx_get_custom_widget_config()
{
    $customConfig = NULL;
    if (isset($_REQUEST['json']))
    {
        $customConfig = stripslashes($_REQUEST['json']);
    }
    return $customConfig;
}

function showcaseidx_display_templated($content)
{
    $templateName = get_option('showcaseidx_template');
    // select template....
    echo get_header($templateName);
    echo $content;
    echo get_footer($templateName);
    exit;
}

function showcaseidx_generate_default_app_url($url)
{
    return "<script>window.location.href = '#{$url}';</script>";    // @todo there's probably a better way to do this
}
