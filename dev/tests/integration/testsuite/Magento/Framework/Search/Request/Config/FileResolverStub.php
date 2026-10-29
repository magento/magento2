<?php
/**
 * Application config file resolver
 *
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Search\Request\Config;

class FileResolverStub implements \Magento\Framework\Config\FileResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($filename, $scope)
    {
        $path = realpath(__DIR__ . '/../../_files/etc');
        $paths = [$path . '/search_request_1.xml', $path . '/search_request_2.xml'];
        return new \Magento\Framework\Config\FileIterator(
            new \Magento\Framework\Filesystem\File\ReadFactory(new \Magento\Framework\Filesystem\DriverPool),
            $paths
        );
    }
}
