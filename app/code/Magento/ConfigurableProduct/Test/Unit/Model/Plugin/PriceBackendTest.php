<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model\Plugin;

use Magento\ConfigurableProduct\Model\Plugin\PriceBackend;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class PriceBackendTest extends \PHPUnit_Framework_TestCase
{
    const CLOSURE_VALUE = 'CLOSURE';
    /** @var  PriceBackend */
    private $priceBackendPlugin;
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $priceAttributeMock;
    /** @var  \Closure */
    private $closure;
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    private $productMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->priceBackendPlugin = $objectManager->getObject('Magento\ConfigurableProduct\Model\Plugin\PriceBackend');

        $this->closure = function () {
            return static::CLOSURE_VALUE;
        };
        $this->priceAttributeMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Attribute\Backend\Price')
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['getTypeId', 'getPriceType', '__wakeUp'])
            ->getMock();
    }

    /**
     * @dataProvider aroundValidateDataProvider
     *
     * @param $typeId
     * @param $expectedResult
     */
    public function testAroundValidate($typeId, $expectedResult)
    {
        $this->productMock->expects($this->any())->method('getTypeId')->will($this->returnValue($typeId));
        $result = $this->priceBackendPlugin->aroundValidate(
            $this->priceAttributeMock,
            $this->closure,
            $this->productMock
        );
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testAroundValidate
     *
     * @return array
     */
    public function aroundValidateDataProvider()
    {
        return [
            ['type' => Configurable::TYPE_CODE, 'result' => true],
            ['type' => Type::TYPE_VIRTUAL, 'result' => static::CLOSURE_VALUE],
        ];
    }
}
