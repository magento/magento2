<?php
/**
 * Public media files entry point
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\App\Cache\Frontend\Factory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\Stdlib\Cookie\PhpCookieReader;

require __DIR__ . '/app/bootstrap.php';

$mediaDirectory = null;
$allowedResources = [];
$configCacheFile = BP . '/var/resource_config.json';

$isAllowed = function ($resource, array $allowedResources) {
    foreach ($allowedResources as $allowedResource) {
        if (0 === stripos($resource, $allowedResource)) {
            return true;
        }
    }
    return false;
};

$request = new \Magento\MediaStorage\Model\File\Storage\Request(
    new Request(
        new PhpCookieReader(),
        new Magento\Framework\Stdlib\StringUtils()
    )
);

if (stripos($relativePath, DirectoryList::MEDIA . '/') === 0) {
    $relativePath = substr($relativePath, strlen(DirectoryList::MEDIA)+1);
}

$relativePath = $request->getPathInfo();
$mediaDirectory = null;
if (file_exists($configCacheFile) && is_readable($configCacheFile)) {
    $config = json_decode(file_get_contents($configCacheFile), true);

    //checking update time
    if (filemtime($configCacheFile) + $config['update_time'] > time()) {
        $allowedResources = $config['allowed_resources'];
        $mediaDirectory = $config['media_directory'];
    }
}

if ($mediaDirectory) {
    // Serve file if it's materialized
    if (!$isAllowed($relativePath, $allowedResources)) {
        require __DIR__ . '/pub/errors/404.php';
    }else{
        $mediaAbsPath = $mediaDirectory . '/' . $relativePath;
        if (is_readable($mediaAbsPath)) {
            if (is_dir($mediaAbsPath)) {
                require __DIR__ . '/pub/errors/404.php';
            }
            $transfer = new \Magento\Framework\File\Transfer\Adapter\Http(
                new \Magento\Framework\HTTP\PhpEnvironment\Response(),
                new \Magento\Framework\File\Mime()
            );
            $transfer->send($mediaAbsPath);
        }
    }
}else{
    // Materialize file in application
    $params = $_SERVER;
    if (empty($mediaDirectory)) {
        $params[ObjectManagerFactory::INIT_PARAM_DEPLOYMENT_CONFIG] = [];
        $params[Factory::PARAM_CACHE_FORCED_OPTIONS] = ['frontend_options' => ['disable_save' => true]];
    }
    $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
    /** @var \Magento\MediaStorage\App\Media $app */
    $app = $bootstrap->createApplication(
        \Magento\MediaStorage\App\Media::class,
        [
            'mediaDirectory' => $mediaDirectory,
            'configCacheFile' => $configCacheFile,
            'isAllowed' => $isAllowed,
            'relativeFileName' => $relativePath,
        ]
    );
    $bootstrap->run($app);
}

