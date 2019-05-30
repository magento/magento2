<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Helper\Wysiwyg;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @magentoAppArea adminhtml
 */
class ImagesTest extends \PHPUnit\Framework\TestCase
{
    public function testGetStorageRoot()
    {
        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Filesystem::class
        );
        $mediaPath = $filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        /** @var \Magento\Cms\Helper\Wysiwyg\Images $helper */
        $helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Cms\Helper\Wysiwyg\Images::class
        );
        $this->assertStringStartsWith($mediaPath, $helper->getStorageRoot());
    }

    /**
     * @magentoConfigFixture default_store admin/url/use_custom 1
     * @magentoConfigFixture default_store admin/url/custom http://backend/
     * @magentoConfigFixture admin_store web/secure/base_url http://backend/
     * @magentoConfigFixture admin_store web/unsecure/base_url http://backend/
     */
    public function testGetCurrentUrl()
    {
        $helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Cms\Helper\Wysiwyg\Images::class
        );
        $this->assertStringStartsWith('http://localhost/', $helper->getCurrentUrl());
    }
}
