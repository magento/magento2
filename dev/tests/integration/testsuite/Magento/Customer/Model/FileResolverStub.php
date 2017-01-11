<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

class FileResolverStub implements \Magento\Framework\Config\FileResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($filename, $scope)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $fileReadFactory = $objectManager->create(\Magento\Framework\Filesystem\File\ReadFactory::class);
        $paths = [realpath(__DIR__ . '/../_files/etc/') . '/extension_attributes.xml'];
        return new \Magento\Framework\Config\FileIterator($fileReadFactory, $paths);
    }
}
