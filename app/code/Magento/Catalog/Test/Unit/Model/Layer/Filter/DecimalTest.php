<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Layer\Filter;

class DecimalTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorRequestVarIsOverwrittenCorrectlyInParent()
    {
        $attributeModel = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            ['getAttributeCode', '__wakeup'],
            [],
            '',
            false
        );
        $attributeModel->expects($this->once())->method('getAttributeCode')->will($this->returnValue('price1'));

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $dataProviderFactory = $this->getMockBuilder(
            \Magento\Catalog\Model\Layer\Filter\DataProvider\DecimalFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $instance = $objectManager->getObject(
            \Magento\Catalog\Model\Layer\Filter\Decimal::class,
            [
                'data' => [
                    'attribute_model' => $attributeModel,
                ],
                'dataProviderFactory' => $dataProviderFactory
            ]
        );
        $this->assertSame('price1', $instance->getRequestVar());
    }
}
