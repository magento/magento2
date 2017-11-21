<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Plugin\ServiceInputProcessor;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Plugin\ServiceInputProcessor\ConvertSpecialPrice;
use Magento\Catalog\Pricing\Price\SpecialPrice;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Webapi\ServiceInputProcessor;
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
            ['mapping' => ['custom_attributes' => 'custom_attributes', 'attribute_code' => 'attribute_code']]
        );
    }

    /**
     * Test ConvertSpecialPrice::aroundProcess convert product special price, if it sets in input array as empty string.
     */
    public function testAroundProcess()
    {
        $inputArray = [
            'product' => [
                CustomAttributesDataInterface::CUSTOM_ATTRIBUTES => [
                    [
                        AttributeInterface::ATTRIBUTE_CODE => SpecialPrice::PRICE_CODE,
                        AttributeInterface::VALUE => '',
                    ],
                ],
            ],
        ];
        /** @var ServiceInputProcessor|\PHPUnit_Framework_MockObject_MockObject $inputProcessorMock */
        $inputProcessorMock = $this->getMockBuilder(ServiceInputProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var ProductInterface|\PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->getMockBuilder(ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCustomAttribute'])
            ->getMockForAbstractClass();
        $product->expects(self::once())
            ->method('setCustomAttribute')
            ->with(self::identicalTo(SpecialPrice::PRICE_CODE), self::identicalTo(''));
        $proceed = function () use ($product) {
            return [$product, false];
        };
        $this->testSubject->aroundProcess(
            $inputProcessorMock,
            $proceed,
            ProductRepositoryInterface::class,
            'save',
            $inputArray
        );
    }
}
