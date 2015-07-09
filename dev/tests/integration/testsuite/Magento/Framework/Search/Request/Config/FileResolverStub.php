<?php
/**
 * Application config file resolver
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Config;

class FileResolverStub implements \Magento\Framework\Config\FileResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($filename, $scope)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $readDirectory = $objectManager->create(
            'Magento\Framework\Filesystem\Directory\Read',
            [
                'driver' => $objectManager->create('Magento\Framework\Filesystem\Driver\File'),
                'path' => realpath(__DIR__ . '/../../_files/etc'),
            ]
        );
        $paths = ['search_request_1.xml', 'search_request_2.xml'];
        return new \Magento\Framework\Config\FileIterator($readDirectory, $paths);
    }
}
