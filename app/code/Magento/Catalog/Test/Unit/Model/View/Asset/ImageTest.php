<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\View\Asset;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Config\CatalogMediaConfig;
use Magento\Catalog\Model\Product\Image\ConvertImageMiscParamsToReadableFormat;
use Magento\Catalog\Model\Product\Media\ConfigInterface;
use Magento\Catalog\Model\View\Asset\Image as ImageAsset;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\View\Asset\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    /** @var ConfigInterface */
    private ConfigInterface $mediaConfig;
    /** @var ContextInterface */
    private ContextInterface $context;
    /** @var EncryptorInterface */
    private EncryptorInterface $encryptor;
    /** @var ImageHelper */
    private ImageHelper $imageHelper;
    /** @var CatalogMediaConfig */
    private CatalogMediaConfig $catalogMediaConfig;
    /** @var StoreManagerInterface */
    private StoreManagerInterface $storeManager;
    /** @var ConvertImageMiscParamsToReadableFormat */
    private ConvertImageMiscParamsToReadableFormat $convertImageMiscParamsToReadableFormat;

    protected function setUp(): void
    {
        $this->mediaConfig = $this->createStub(ConfigInterface::class);
        $this->context = $this->createStub(ContextInterface::class);
        $this->encryptor = $this->createStub(EncryptorInterface::class);
        $this->imageHelper = $this->createStub(ImageHelper::class);
        $this->catalogMediaConfig = $this->createStub(CatalogMediaConfig::class);
        $this->storeManager = $this->createStub(StoreManagerInterface::class);
        $this->convertImageMiscParamsToReadableFormat =
            $this->createStub(ConvertImageMiscParamsToReadableFormat::class);
    }

    public function testGetPath(): void
    {
        $filePath = 'i/m/image.jpg';
        $miscParams = [];

        $imageAsset = new ImageAsset(
            $this->mediaConfig,
            $this->context,
            $this->encryptor,
            $filePath,
            $miscParams,
            $this->imageHelper,
            $this->catalogMediaConfig,
            $this->storeManager,
            $this->convertImageMiscParamsToReadableFormat,
        );

        $this->assertStringEndsWith($filePath, $imageAsset->getPath());
    }

    public function testGetPathWithPercentageCharacter(): void
    {
        $filePath = 'i/m/im%age.jpg';
        $miscParams = [];

        $imageAsset = new ImageAsset(
            $this->mediaConfig,
            $this->context,
            $this->encryptor,
            $filePath,
            $miscParams,
            $this->imageHelper,
            $this->catalogMediaConfig,
            $this->storeManager,
            $this->convertImageMiscParamsToReadableFormat,
        );

        $this->assertStringEndsWith($filePath, $imageAsset->getPath());
    }
}
