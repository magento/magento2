<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryIntegration\Test\Integration\Model;

use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Cms\Model\Wysiwyg\Gallery\DefaultConfigProvider;
use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Provide integration tests cover update open dialog url functionality for media editor.
 * @magentoAppArea adminhtml
 */
class GalleryConfigProviderOpenDialogUrlTest extends TestCase
{
    private const FILES_BROWSER_WINDOW_URL = 'files_browser_window_url';
    private const MEDIA_GALLERY_URL = 'media_gallery/index/index';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManger;

    /**
     * @var DefaultConfigProvider
     */
    private $galleryConfigProvider;

    /**
     * @var DataObject
     */
    private $configDataObject;

    /**
     * @var string
     */
    private $fileBrowserWindowUrl;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManger = Bootstrap::getObjectManager();
        $this->galleryConfigProvider = $this->objectManger->create(DefaultConfigProvider::class);
        $this->configDataObject = $this->objectManger->create(DataObject::class);

        $url = $this->objectManger->create(UrlInterface::class);

        $this->fileBrowserWindowUrl = $url->getUrl(
            self::MEDIA_GALLERY_URL,
            [
                'current_tree_path' => $this->objectManger->get(Images::class)->idEncode(Config::IMAGE_DIRECTORY),
            ]
        );
    }

    /**
     * Test image open dialog url when enhanced media gallery not enabled.
     * @magentoConfigFixture default/system/media_gallery/enabled 0
     */
    public function testWithEnhancedMediaGalleryDisabled(): void
    {
        $config = $this->galleryConfigProvider->getConfig($this->configDataObject);
        self::assertNotEquals($this->fileBrowserWindowUrl, $config->getData(self::FILES_BROWSER_WINDOW_URL));
    }

    /**
     * Test image open dialog url when enhanced media gallery enabled.
     * @magentoConfigFixture default/system/media_gallery/enabled 1
     */
    public function testWithEnhancedMediaGalleryEnabled(): void
    {
        $config = $this->galleryConfigProvider->getConfig($this->configDataObject);
        self::assertEquals($this->fileBrowserWindowUrl, $config->getData(self::FILES_BROWSER_WINDOW_URL));
    }
}
