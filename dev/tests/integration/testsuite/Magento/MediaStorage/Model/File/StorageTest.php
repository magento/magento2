<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Model\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;

class StorageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * test for \Magento\MediaStorage\Model\File\Storage::getScriptConfig()
     *
     * @magentoConfigFixture current_store system/media_storage_configuration/configuration_update_time 1000
     */
    public function testGetScriptConfig()
    {
        $config = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\MediaStorage\Model\File\Storage::class
        )->getScriptConfig();
        $this->assertIsArray($config);
        $this->assertArrayHasKey('media_directory', $config);
        $this->assertArrayHasKey('allowed_resources', $config);
        $this->assertArrayHasKey('update_time', $config);
        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Filesystem::class
        );

        $mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        if ($mediaDirectory->getDriver() instanceof File) {
            $this->assertEquals($config['media_directory'], $mediaDirectory->getAbsolutePath());
        }
        $this->assertIsArray($config['allowed_resources']);
        $this->assertContains('css', $config['allowed_resources']);
        $this->assertContains('css_secure', $config['allowed_resources']);
        $this->assertContains('js', $config['allowed_resources']);
        $this->assertContains('theme', $config['allowed_resources']);
        $this->assertEquals(1000, $config['update_time']);
    }
}
