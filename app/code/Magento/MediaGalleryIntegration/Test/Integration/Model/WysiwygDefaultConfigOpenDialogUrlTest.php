<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryIntegration\Test\Integration\Model;

use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Cms\Model\Wysiwyg\Gallery\DefaultConfigProvider;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Provide integration tests cover update wysiwyg editor dialog url update when media gallery enabled.
 * @magentoAppArea adminhtml
 */
class WysiwygDefaultConfigOpenDialogUrlTest extends TestCase
{
    private const FILES_BROWSER_WINDOW_URL = 'files_browser_window_url';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManger;

    /**
     * @var DataObject
     */
    private $configDataObject;

    /**
     * @var string
     */
    private $filesBrowserWindowUrl;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManger = Bootstrap::getObjectManager();
        $this->configDataObject = $this->objectManger->create(DataObject::class);

        $url = $this->objectManger->create(UrlInterface::class);
        $imageHelper = $this->objectManger->create(Images::class);
        $this->filesBrowserWindowUrl = $url->getUrl(
            'media_gallery/index/index',
            ['current_tree_path' => $imageHelper->idEncode(Config::IMAGE_DIRECTORY)]
        );
    }

    /**
     * Test update wysiwyg editor open dialog url when enhanced media gallery not enabled.
     * @magentoConfigFixture default/system/media_gallery/enabled 0
     */
    public function testWithEnhancedMediaGalleryDisabled(): void
    {
        /** @var DefaultConfigProvider $defaultConfigProvider */
        $defaultConfigProvider = $this->objectManger->create(DefaultConfigProvider::class);
        $config = $defaultConfigProvider->getConfig($this->configDataObject);
        self::assertNotEquals($this->filesBrowserWindowUrl, $config->getData(self::FILES_BROWSER_WINDOW_URL));
    }

    /**
     * Test update wysiwyg editor open dialog url when enhanced media gallery enabled.
     * @magentoConfigFixture default/system/media_gallery/enabled 1
     */
    public function testWithEnhancedMediaGalleryEnabled(): void
    {
        /** @var DefaultConfigProvider $defaultConfigProvider */
        $defaultConfigProvider = $this->objectManger->create(DefaultConfigProvider::class);
        $config = $defaultConfigProvider->getConfig($this->configDataObject);
        self::assertEquals($this->filesBrowserWindowUrl, $config->getData(self::FILES_BROWSER_WINDOW_URL));
    }
}
