<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
        $readDirectory = $objectManager->create(
            'Magento\Framework\Filesystem\Directory\Read',
            [
                'driver' => $objectManager->create('Magento\Framework\Filesystem\Driver\File'),
                'path' => realpath(__DIR__ . '/../_files/etc'),
            ]
        );
        $paths = ['data_object.xml'];
        return new \Magento\Framework\Config\FileIterator($readDirectory, $paths);
    }
}
