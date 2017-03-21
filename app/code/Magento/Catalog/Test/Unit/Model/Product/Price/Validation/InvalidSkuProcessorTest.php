<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Price\Validation;

/**
 * Test for model Magento\Catalog\Product\Price\Validation\InvalidSkuProcessor.
 */
class InvalidSkuProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor
     */
    private $invalidSkuProcessor;

    /**
     * @var \Magento\Catalog\Model\ProductIdLocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productIdLocator;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->productIdLocator = $this->getMockBuilder(\Magento\Catalog\Model\ProductIdLocatorInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->invalidSkuProcessor = $objectManager->getObject(
            \Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor::class,
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
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->setMethods(['getPriceType'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
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
        $productType = \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE;
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
