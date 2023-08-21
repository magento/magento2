<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Block\Product\Renderer\Configurable\Listing;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Swatches\Block\Product\Renderer\Listing\Configurable;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests for configurable products options block with swatch attribute.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea frontend
 */
class ConfigurableTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var Configurable
     */
    private $block;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Manager $moduleManager */
        $moduleManager = $objectManager->get(Manager::class);
        if (!$moduleManager->isEnabled('Magento_Catalog')) {
            self::markTestSkipped('Magento_Catalog module disabled.');
        }
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->serializer = $this->objectManager->get(SerializerInterface::class);
        $this->productAttributeRepository = $this->objectManager->get(ProductAttributeRepositoryInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Configurable::class);
        $this->request = $this->objectManager->get(RequestInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Swatches/_files/configurable_product_with_images.php
     * @return void
     */
    public function testPreSelectedGalleryConfig(): void
    {
        $product = $this->productRepository->get('configurable');
        $this->block->setProduct($product);
        $configurableAttribute = $this->productAttributeRepository->get('visual_swatch_attribute');
        $this->request->setQueryValue('visual_swatch_attribute', $configurableAttribute->getOptions()[1]->getValue());
        $jsonConfig = $this->serializer->unserialize($this->block->getJsonConfig());
        $this->assertArrayHasKey('preSelectedGallery', $jsonConfig);
        $this->assertStringEndsWith('/m/a/magento_image.jpg', $jsonConfig['preSelectedGallery']['large']);
        $this->assertStringEndsWith('/m/a/magento_image.jpg', $jsonConfig['preSelectedGallery']['medium']);
        $this->assertStringEndsWith('/m/a/magento_image.jpg', $jsonConfig['preSelectedGallery']['small']);
    }
}
