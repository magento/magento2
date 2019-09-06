<?php
/**
 * Public media files entry point
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\Stdlib\Cookie\PhpCookieReader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

require_once __DIR__ . '/../app/bootstrap.php';

$filesystem = new FileSystem(
    new Directorylist(__DIR__ . '/..', Directorylist::getDefaultConfig()),
    new Filesystem\Directory\ReadFactory(new Filesystem\DriverPool()),
    new Filesystem\Directory\WriteFactory(new Filesystem\DriverPool())
);

$request = new \Magento\MediaStorage\Model\File\Storage\Request(
    new Request(
        new PhpCookieReader(),
        new Magento\Framework\Stdlib\StringUtils()
    )
);

// Serve file if it's materialized
$mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath();
$relativePath = substr($request->getPathInfo(), strlen(DirectoryList::MEDIA) + 1);
$mediaAbsPath = $mediaDirectory . '/' . $relativePath;
if (is_readable($mediaAbsPath) && !is_dir($mediaAbsPath)) {
    $transfer = new \Magento\Framework\File\Transfer\Adapter\Http(
        new \Magento\Framework\HTTP\PhpEnvironment\Response(),
        new \Magento\Framework\File\Mime()
    );
    $transfer->send($mediaAbsPath);
} else {
    $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, array());
    /**
 * @var \Magento\MediaStorage\App\Media $app 
*/
    $app = $bootstrap->createApplication(
        \Magento\MediaStorage\App\Media::class,
        [
            'relativeFileName' => $relativePath
        ]
    );
    $bootstrap->run($app);
}