<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryIntegration\Test\Integration\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Ui\Component\Form\Element\DataType\Media\Image;
use PHPUnit\Framework\TestCase;

/**
 * Provide integration tests cover update open dialog url functionality for media editor.
 * @magentoAppArea adminhtml
 */
class ImageComponentOpenDialogUrlTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManger;

    /**
     * @var Image
     */
    private $image;

    /**
     * @var string
     */
    private $mediaGalleryOpenDialogUrl;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManger = Bootstrap::getObjectManager();
        $this->image = $this->objectManger->create(Image::class);
        $this->image->setData('config', ['initialMediaGalleryOpenSubpath' => 'wysiwyg']);

        $url = $this->objectManger->create(UrlInterface::class);
        $this->mediaGalleryOpenDialogUrl = $url->getUrl('media_gallery/index/index');
    }

    /**
     * Test image open dialog url when enhanced media gallery not enabled.
     * @magentoConfigFixture default/system/media_gallery/enabled 0
     */
    public function testWithEnhancedMediaGalleryDisabled(): void
    {
        $this->image->prepare();
        $expectedOpenDialogUrl = $this->image->getConfiguration()['mediaGallery']['openDialogUrl'];
        self::assertNotEquals($this->mediaGalleryOpenDialogUrl, $expectedOpenDialogUrl);
    }

    /**
     * Test image open dialog url when enhanced media gallery enabled.
     * @magentoConfigFixture default/system/media_gallery/enabled 1
     */
    public function testWithEnhancedMediaGalleryEnabled(): void
    {
        $this->image->prepare();
        $expectedOpenDialogUrl = $this->image->getConfiguration()['mediaGallery']['openDialogUrl'];
        self::assertEquals($this->mediaGalleryOpenDialogUrl, $expectedOpenDialogUrl);
    }
}
