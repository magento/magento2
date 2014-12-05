<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Bundle\Model\Plugin;

class BundleLoadOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Model\Plugin\BundleLoadOptions
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeBuilderMock;

    protected function setUp()
    {
        $this->optionListMock = $this->getMock('\Magento\Bundle\Model\Product\OptionList', [], [], '', false);
        $this->attributeBuilderMock = $this->getMock('\Magento\Framework\Api\AttributeDataBuilder', [], [], '', false);
        $this->model = new \Magento\Bundle\Model\Plugin\BundleLoadOptions(
            $this->optionListMock,
            $this->attributeBuilderMock
        );
    }

    public function testAroundLoadIfProductTypeNotBundle()
    {
        $productMock = $this->getMock('Magento\Catalog\Model\Product', ['getTypeId'], [], '', false);
        $closure = function () use ($productMock) {
            return $productMock;
        };
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $this->assertEquals(
            $productMock,
            $this->model->aroundLoad($productMock, $closure, 100, null)
        );
    }

    public function testAroundLoad()
    {
        $productMock = $this->getMock(
            '\Magento\Catalog\Model\Product',
            ['getTypeId', 'getCustomAttributes', 'setData'],
            [],
            '',
            false
        );
        $closure = function () use ($productMock) {
            return $productMock;
        };
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);

        $optionMock = $this->getMock('\Magento\Bundle\Api\Data\OptionInterface');
        $this->optionListMock->expects($this->once())
            ->method('getItems')
            ->with($productMock)
            ->willReturn([$optionMock]);
        $this->attributeBuilderMock->expects($this->once())
            ->method('setAttributeCode')
            ->with('bundle_product_options')
            ->willReturnSelf();
        $this->attributeBuilderMock->expects($this->once())
            ->method('setValue')
            ->with([$optionMock])
            ->willReturnSelf();
        $customAttributeMock = $this->getMock('\Magento\Framework\Api\AttributeValue', [], [], '', false);
        $this->attributeBuilderMock->expects($this->once())->method('create')->willReturn($customAttributeMock);

        $productAttributeMock = $this->getMock('\Magento\Framework\Api\AttributeValue', [], [], '', false);
        $productMock->expects($this->once())->method('getCustomAttributes')->willReturn([$productAttributeMock]);
        $productMock->expects($this->once())
            ->method('setData')
            ->with('custom_attributes', ['bundle_product_options' => $customAttributeMock, $productAttributeMock])
            ->willReturnSelf();

        $this->assertEquals(
            $productMock,
            $this->model->aroundLoad($productMock, $closure, 100, null)
        );
    }
}
