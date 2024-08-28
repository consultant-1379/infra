<?php
/**
 * Wordpress Functions Entry Point
 *
 * PHP version 7.1
 *
 * @category WP_Functions
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */

require_once 'api/settings.php';

require_once 'api/hooks/AdminMenu.hook.php';
require_once 'api/hooks/AdminSettings.hook.php';
require_once 'api/hooks/MenuBuilder.hook.php';
require_once 'api/hooks/PagePostPreview.hook.php';
require_once 'api/hooks/Pages.hook.php';
require_once 'api/hooks/TutorialPages.hook.php';
require_once 'api/hooks/Category.hook.php';
require_once 'api/hooks/ApiRequest.hook.php';

require_once 'api/Routes.php';


use api\Hooks\AdminMenuHook;
use api\Hooks\AdminSettingsHook;
use api\Hooks\MenuBuilderHook;
use api\Hooks\PagePostPreviewHook;
use api\Hooks\PagesHook;
use api\Hooks\TutorialPagesHook;
use api\Hooks\CategoryHook;
use api\Hooks\ApiRequestHook;

new AdminMenuHook();
new AdminSettingsHook();
new MenuBuilderHook();
new PagePostPreviewHook();
new PagesHook();
new TutorialPagesHook();
new CategoryHook();
new ApiRequestHook();