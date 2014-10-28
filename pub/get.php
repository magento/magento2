<?php
/**
 * Public media files entry point
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

use Magento\Framework\App\Cache\Frontend\Factory;
use Magento\Framework\Module\Declaration\Reader\Filesystem;

require dirname(__DIR__) . '/app/bootstrap.php';

$mediaDirectory = null;
$allowedResources = array();
$configCacheFile = dirname(__DIR__) . '/var/resource_config.json';
$relativeFilename = null;

$isAllowed = function ($resource, array $allowedResources) {
    $isResourceAllowed = false;
    foreach ($allowedResources as $allowedResource) {
        if (0 === stripos($resource, $allowedResource)) {
            $isResourceAllowed = true;
        }
    }
    return $isResourceAllowed;
};

if (file_exists($configCacheFile) && is_readable($configCacheFile)) {
    $config = json_decode(file_get_contents($configCacheFile), true);

    //checking update time
    if (filemtime($configCacheFile) + $config['update_time'] > time()) {
        $mediaDirectory = trim(str_replace(__DIR__, '', $config['media_directory']), '/');
        $allowedResources = array_merge($allowedResources, $config['allowed_resources']);
    }
}

// Serve file if it's materialized
$request = new \Magento\Core\Model\File\Storage\Request(__DIR__);
if ($mediaDirectory) {
    if (0 !== stripos($request->getPathInfo(), $mediaDirectory . '/') || is_dir($request->getFilePath())) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }

    $relativeFilename = str_replace($mediaDirectory . '/', '', $request->getPathInfo());
    if (!$isAllowed($relativeFilename, $allowedResources)) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }

    if (is_readable($request->getFilePath())) {
        $transfer = new \Magento\Framework\File\Transfer\Adapter\Http(
            new \Magento\Framework\Controller\Response\Http,
            new \Magento\Framework\File\Mime
        );
        $transfer->send($request->getFilePath());
        exit;
    }
}
// Materialize file in application
$params = $_SERVER;
if (empty($mediaDirectory)) {
    $params[Filesystem::PARAM_ALLOWED_MODULES] = ['Magento_Core'];
    $params[Factory::PARAM_CACHE_FORCED_OPTIONS] = ['frontend_options' => ['disable_save' => true]];
}
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
/** @var \Magento\Core\App\Media $app */
$app = $bootstrap->createApplication(
    'Magento\Core\App\Media',
    [
        'request' => $request,
        'workingDirectory' => __DIR__,
        'mediaDirectory' => $mediaDirectory,
        'configCacheFile' => $configCacheFile,
        'isAllowed' => $isAllowed,
        'relativeFileName' => $relativeFilename,
    ]
);
$bootstrap->run($app);
