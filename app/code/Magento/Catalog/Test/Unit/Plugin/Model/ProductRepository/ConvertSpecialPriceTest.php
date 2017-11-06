<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Plugin\Model\ProductRepository;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Plugin\Model\ProductRepository\ConvertSpecialPrice;
use Magento\Catalog\Pricing\Price\SpecialPrice;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Webapi\Rest\Request;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for ConvertSpecialPrice plugin.
 */
class ConvertSpecialPriceTest extends TestCase
{
    /**
     * @var ConvertSpecialPrice
     */
    private $testSubject;

    /**
     * @var Request|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->requestMock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->testSubject = $objectManager->getObject(
            ConvertSpecialPrice::class,
            ['request' => $this->requestMock]
        );
    }

    /**
     * Test ConvertSpecialPrice::beforeSave convert product special price, if it sets in request as empty string.
     */
    public function testBeforeSave()
    {
        $bodyParams = [
            'product' => [
                CustomAttributesDataInterface::CUSTOM_ATTRIBUTES => [
                    [
                        AttributeInterface::ATTRIBUTE_CODE => SpecialPrice::PRICE_CODE,
                        AttributeInterface::VALUE => '',
                    ],
                ],
            ],
        ];
        $this->requestMock->expects(self::once())
            ->method('getBodyParams')
            ->willReturn($bodyParams);
        /** @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject $productRepository */
        $productRepository = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        /** @var ProductInterface|\PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->getMockBuilder(ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCustomAttribute'])
            ->getMockForAbstractClass();
        $product->expects(self::once())
            ->method('setCustomAttribute')
            ->with(self::identicalTo(SpecialPrice::PRICE_CODE), self::identicalTo(''));
        $this->testSubject->beforeSave($productRepository, $product);
    }
}
