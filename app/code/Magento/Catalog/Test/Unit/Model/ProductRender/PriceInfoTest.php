<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ProductRender;

use Magento\Catalog\Model\ProductRender\PriceInfo;

class PriceInfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ProductRender\PriceInfo
     */
    private $priceInfo;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $extensionFactoryMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    private $registryMock;

    /**
     * @var \Magento\Framework\Api\AttributeValueFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $attributeValueFactoryMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\Model\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->extensionFactoryMock = $this->getMockBuilder(\Magento\Framework\Api\ExtensionAttributesFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeValueFactoryMock = $this->getMockBuilder(\Magento\Framework\Api\AttributeValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->priceInfo = new PriceInfo(
            $this->contextMock,
            $this->registryMock,
            $this->extensionFactoryMock,
            $this->attributeValueFactoryMock
        );
    }

    public function testGetMaxRegularPrice()
    {
        $maxRegularPriceValue = 123;

        $this->priceInfo->setMaxRegularPrice($maxRegularPriceValue);

        $this->assertEquals($this->priceInfo->getMaxRegularPrice(), $maxRegularPriceValue);
    }

    public function testEmptyMaxRegularPrice()
    {
        $maxRegularPriceValue = 123;

        $this->priceInfo->setMaxPrice($maxRegularPriceValue);

        $this->assertEquals($this->priceInfo->getMaxRegularPrice(), $maxRegularPriceValue);
    }

    public function testGetMinRegularPrice()
    {
        $minRegularPriceValue = 13;

        $this->priceInfo->setMinimalRegularPrice($minRegularPriceValue);

        $this->assertEquals($this->priceInfo->getMinimalRegularPrice(), $minRegularPriceValue);
    }

    public function testEmptyMinRegularPrice()
    {
        $minPriceValue = 12;

        $this->priceInfo->setMinimalPrice($minPriceValue);

        $this->assertEquals($this->priceInfo->getMinimalRegularPrice(), $minPriceValue);
    }
}
