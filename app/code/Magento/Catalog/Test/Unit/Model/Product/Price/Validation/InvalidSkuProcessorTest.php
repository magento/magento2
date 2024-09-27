<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Price\Validation;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductIdLocatorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for model Magento\Catalog\Product\Price\Validation\InvalidSkuProcessor.
 */
class InvalidSkuProcessorTest extends TestCase
{
    /**
     * @var InvalidSkuProcessor
     */
    private $invalidSkuProcessor;

    /**
     * @var ProductIdLocatorInterface|MockObject
     */
    private $productIdLocator;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->productIdLocator = $this->getMockBuilder(ProductIdLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productRepository = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->invalidSkuProcessor = $objectManager->getObject(
            InvalidSkuProcessor::class,
            [
                'productIdLocator' => $this->productIdLocator,
                'productRepository' => $this->productRepository
            ]
        );
    }

    /**
     * Prepare retrieveInvalidSkuList().
     *
     * @param string $productType
     * @param string $productSku
     * @return void
     */
    private function prepareRetrieveInvalidSkuListMethod($productType, $productSku)
    {
        $idsBySku = [$productSku => [235235235 => $productType]];
        $this->productIdLocator->expects($this->atLeastOnce())->method('retrieveProductIdsBySkus')
            ->willReturn($idsBySku);
        $product = $this->getMockBuilder(ProductInterface::class)
            ->addMethods(['getPriceType'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productPriceType = 0;
        $product->expects($this->atLeastOnce())->method('getPriceType')->willReturn($productPriceType);
        $this->productRepository->expects($this->atLeastOnce())->method('get')->willReturn($product);
    }

    /**
     * Test for retrieveInvalidSkuList().
     *
     * @return void
     */
    public function testRetrieveInvalidSkuList()
    {
        $productSku = 'LKJKJ2233636';
        $productType = Type::TYPE_BUNDLE;
        $methodParamSku = 'SDFSDF3242355';
        $skus = [$methodParamSku];
        $allowedProductTypes = [$productType];
        $allowedPriceTypeValue = true;
        $this->prepareRetrieveInvalidSkuListMethod($productType, $productSku);

        $this->assertEquals(
            [$methodParamSku, $productSku],
            $this->invalidSkuProcessor->retrieveInvalidSkuList($skus, $allowedProductTypes, $allowedPriceTypeValue)
        );
    }
}
