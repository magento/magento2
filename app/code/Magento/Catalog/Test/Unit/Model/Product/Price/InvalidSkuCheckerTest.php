<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Price;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test for model Product\Price\InvalidSkuChecker.
 */
class InvalidSkuCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Price\InvalidSkuChecker
     */
    private $invalidSkuChecker;

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
            ->setMethods(['retrieveProductIdsBySkus'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->invalidSkuChecker = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\Product\Price\InvalidSkuChecker::class,
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

        $expects = [$methodParamSku, $productSku];
        $result = $this->invalidSkuChecker->retrieveInvalidSkuList($skus, $allowedProductTypes, $allowedPriceTypeValue);
        $this->assertEquals($expects, $result);
    }

    /**
     * Test for isSkuListValid() with Exception.
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testIsSkuListValidWithException()
    {
        $productSku = 'LKJKJ2233636';
        $productType = \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE;
        $methodParamSku = 'SDFSDF3242355';
        $skus = [$methodParamSku];
        $allowedProductTypes = [$productType];
        $allowedPriceTypeValue = true;

        $this->prepareRetrieveInvalidSkuListMethod($productType, $productSku);

        $this->invalidSkuChecker->isSkuListValid($skus, $allowedProductTypes, $allowedPriceTypeValue);
    }
}
