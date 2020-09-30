<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryIntegration\Test\Integration\Model;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Tinymce3\Model\Config\Gallery\Config;
use PHPUnit\Framework\TestCase;

/**
 * Provide integration tests cover update open dialog url functionality for media editor.
 * @magentoAppArea adminhtml
 */
class TinyMceOpenDialogUrlTest extends TestCase
{
    private const FILES_BROWSER_WINDOW_URL = 'files_browser_window_url';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManger;

    /**
     * @var Config
     */
    private $tinyMce3Config;

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
        $this->tinyMce3Config = $this->objectManger->create(Config::class);
        $this->configDataObject = $this->objectManger->create(DataObject::class);

        $url = $this->objectManger->create(UrlInterface::class);
        $this->fileBrowserWindowUrl = $url->getUrl('media_gallery/index/index');
    }

    /**
     * Test image open dialog url when enhanced media gallery not enabled.
     * @magentoConfigFixture default/system/media_gallery/enabled 0
     */
    public function testWithEnhancedMediaGalleryDisabled(): void
    {
        $config = $this->tinyMce3Config->getConfig($this->configDataObject);
        self::assertNotEquals($this->fileBrowserWindowUrl, $config->getData(self::FILES_BROWSER_WINDOW_URL));
    }

    /**
     * Test image open dialog url when enhanced media gallery enabled.
     * @magentoConfigFixture default/system/media_gallery/enabled 1
     */
    public function testWithEnhancedMediaGalleryEnabled(): void
    {
        $config = $this->tinyMce3Config->getConfig($this->configDataObject);
        self::assertEquals($this->fileBrowserWindowUrl, $config->getData(self::FILES_BROWSER_WINDOW_URL));
    }
}
