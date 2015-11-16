<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
        $fileReadFactory = $objectManager->create('Magento\Framework\Filesystem\File\ReadFactory');
        $paths = [realpath(__DIR__ . '/../_files/etc/') . '/extension_attributes.xml'];
        return new \Magento\Framework\Config\FileIterator($fileReadFactory, $paths);
    }
}
