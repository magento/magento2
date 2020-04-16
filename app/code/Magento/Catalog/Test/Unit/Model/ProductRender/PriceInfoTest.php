<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ProductRender;

use Magento\Catalog\Model\ProductRender\PriceInfo;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PriceInfoTest extends TestCase
{
    /**
     * @var PriceInfo
     */
    private $priceInfo;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var ExtensionAttributesFactory|MockObject
     */
    private $extensionFactoryMock;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var AttributeValueFactory|MockObject
     */
    private $attributeValueFactoryMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->extensionFactoryMock = $this->getMockBuilder(ExtensionAttributesFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeValueFactoryMock = $this->getMockBuilder(AttributeValueFactory::class)
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
