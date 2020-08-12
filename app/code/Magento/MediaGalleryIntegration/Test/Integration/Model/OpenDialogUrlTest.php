<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryIntegration\Test\Integration\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\MediaGalleryUiApi\Api\ConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Ui\Component\Form\Element\DataType\Media\OpenDialogUrl;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests cover getting correct url based on the config settings.
 * @magentoAppArea adminhtml
 */
class OpenDialogUrlTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManger;

    /**
     * @var OpenDialogUrl
     */
    private $openDialogUrl;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManger = Bootstrap::getObjectManager();
        $config = $this->objectManger->create(ConfigInterface::class);
        $this->openDialogUrl = $this->objectManger->create(
            OpenDialogUrl::class,
            ['config' => $config]
        );
    }

    /**
     * Test getting open dialog url with enhanced media gallery disabled.
     * @magentoConfigFixture default/system/media_gallery/enabled 0
     */
    public function testWithEnhancedMediaGalleryDisabled(): void
    {
        self::assertEquals('cms/wysiwyg_images/index', $this->openDialogUrl->get());
    }

    /**
     * Test getting open dialog url when enhanced media gallery enabled.
     * @magentoConfigFixture default/system/media_gallery/enabled 1
     */
    public function testWithEnhancedMediaGalleryEnabled(): void
    {
        self::assertEquals('media_gallery/index/index', $this->openDialogUrl->get());
    }
}
