<?php
/**
 * Public media files entry point
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\App\Cache\Frontend\Factory;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\Module\ModuleList\DeploymentConfig;

require dirname(__DIR__) . '/app/bootstrap.php';

$mediaDirectory = null;
$allowedResources = [];
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
$request = new \Magento\MediaStorage\Model\File\Storage\Request(__DIR__);
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
            new \Magento\Framework\HTTP\PhpEnvironment\Response(),
            new \Magento\Framework\File\Mime()
        );
        $transfer->send($request->getFilePath());
        exit;
    }
}
// Materialize file in application
$params = $_SERVER;
if (empty($mediaDirectory)) {
    $params[ObjectManagerFactory::INIT_PARAM_DEPLOYMENT_CONFIG] = [];
    $params[Factory::PARAM_CACHE_FORCED_OPTIONS] = ['frontend_options' => ['disable_save' => true]];
}
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
/** @var \Magento\MediaStorage\App\Media $app */
$app = $bootstrap->createApplication(
    'Magento\MediaStorage\App\Media',
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
