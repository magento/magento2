<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\ProductList;

use Magento\Catalog\Model\Product\ProductList\Toolbar;
use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class for testing toolbal memorizer.
 */
class ToolbarMemorizerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ToolbarMemorizer
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Toolbar
     */
    private $toolbarMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CatalogSession
     */
    private $catalogSessionMock;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfigMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->toolbarMock = $this->getMockBuilder(Toolbar::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrder', 'getDirection', 'getLimit', 'getMode'])
            ->getMock();
        $this->catalogSessionMock = $this->getMockBuilder(CatalogSession::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParamsMemorizeDisabled', 'getData'])
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            ToolbarMemorizer::class,
            [
                'toolbarModel' => $this->toolbarMock,
                'catalogSession' => $this->catalogSessionMock,
                'scopeConfig' => $this->scopeConfigMock,
            ]
        );
    }

    /**
     * @return array
     */
    public function getMainDataProvider(): array
    {
        return [
            ['any_value',null,null,null,'any_value'],
            [null, 'any_value', false, null, 'any_value'],
            [null, null, false, null, null],
            [null, null, true, 'data', 'data'],
        ];
    }

    /**
     * Test get order.
     *
     * @param string|null $variable
     * @param string|null $variableValue
     * @param bool|null $flag
     * @param string|null $data
     * @param string|null $expected
     * @return void
     *
     * @dataProvider getMainDataProvider
     */
    public function testGetOrder($variable, $variableValue, $flag, $data, $expected)
    {
        $this->setPropertyValue($this->model, 'order', $variable);

        $this->toolbarMock->method('getOrder')->willReturn($variableValue);
        $this->scopeConfigMock->method('isSetFlag')->willReturn($flag);
        $this->catalogSessionMock->method('getData')->willReturn($data);
        $this->assertEquals($expected, $this->model->getOrder());
    }

    /**
     * Test get direction.
     *
     * @param string|null $variable
     * @param string|null $variableValue
     * @param bool|null $flag
     * @param string|null $data
     * @param string|null $expected
     * @return void
     *
     * @dataProvider getMainDataProvider
     */
    public function testGetDirection($variable, $variableValue, $flag, $data, $expected)
    {
        $this->setPropertyValue($this->model, 'direction', $variable);

        $this->toolbarMock->method('getDirection')->willReturn($variableValue);
        $this->scopeConfigMock->method('isSetFlag')->willReturn($flag);
        $this->catalogSessionMock->method('getData')->willReturn($data);
        $this->assertEquals($expected, $this->model->getDirection());
    }

    /**
     * Test get mode.
     *
     * @param string|null $variable
     * @param string|null $variableValue
     * @param bool|null $flag
     * @param string|null $data
     * @param string|null $expected
     * @return void
     *
     * @dataProvider getMainDataProvider
     */
    public function testGetMode($variable, $variableValue, $flag, $data, $expected)
    {
        $this->setPropertyValue($this->model, 'mode', $variable);

        $this->toolbarMock->method('getMode')->willReturn($variableValue);
        $this->scopeConfigMock->method('isSetFlag')->willReturn($flag);
        $this->catalogSessionMock->method('getData')->willReturn($data);
        $this->assertEquals($expected, $this->model->getMode());
    }

    /**
     * Test getting limit.
     *
     * @param string|null $variable
     * @param string|null $variableValue
     * @param bool|null $flag
     * @param string|null $data
     * @param string|null $expected
     * @return void
     *
     * @dataProvider getMainDataProvider
     */
    public function testGetLimit($variable, $variableValue, $flag, $data, $expected)
    {
        $this->setPropertyValue($this->model, 'limit', $variable);

        $this->toolbarMock->method('getLimit')->willReturn($variableValue);
        $this->scopeConfigMock->method('isSetFlag')->willReturn($flag);
        $this->catalogSessionMock->method('getData')->willReturn($data);
        $this->assertEquals($expected, $this->model->getLimit());
    }

    /**
     * Test memorizing parameters.
     */
    public function testMemorizeParams()
    {
        $this->catalogSessionMock->method('getParamsMemorizeDisabled')->willReturn(false);
        $this->setPropertyValue($this->model, 'isMemorizingAllowed', true);
        $this->model->memorizeParams();
    }

    /**
     * @return array
     */
    public function getMemorizedDataProvider(): array
    {
        return [
            [null, false, false],
            [null, true, true],
            [false, false, false],
            [false, true, false],
            [true, false, true],
            [true, true, true],
        ];
    }

    /**
     * Test method isMemorizingAllowed.
     *
     * @aram bool|null $variableValue
     * @param bool $flag
     * @param bool $expected
     * @return void
     *
     * @dataProvider getMemorizedDataProvider
     */
    public function testIsMemorizingAllowed($variableValue, bool $flag, bool $expected)
    {
        $this->setPropertyValue($this->model, 'isMemorizingAllowed', $variableValue);
        $this->scopeConfigMock->method('isSetFlag')->willReturn($flag);
        $this->assertEquals($expected, $this->model->isMemorizingAllowed());
    }

    /**
     * Set object property value.
     *
     * @param object $object
     * @param string $property
     * @param string|bool|null $value
     *
     * @return object
     */
    private function setPropertyValue(&$object, string $property, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);

        return $object;
    }
}
