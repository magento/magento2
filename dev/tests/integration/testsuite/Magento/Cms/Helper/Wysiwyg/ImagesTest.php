<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Helper\Wysiwyg;

use Magento\Framework\App\Filesystem\DirectoryList;

class ImagesTest extends \PHPUnit_Framework_TestCase
{
    public function testGetStorageRoot()
    {
        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Filesystem'
        );
        $mediaPath = $filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        /** @var \Magento\Cms\Helper\Wysiwyg\Images $helper */
        $helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Cms\Helper\Wysiwyg\Images'
        );
        $this->assertStringStartsWith($mediaPath, $helper->getStorageRoot());
    }

    public function testGetCurrentUrl()
    {
        $helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Cms\Helper\Wysiwyg\Images'
        );
        $this->assertStringStartsWith('http://localhost/', $helper->getCurrentUrl());
    }
}
