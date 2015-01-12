<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer\Filter;

class DecimalTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorRequestVarIsOverwrittenCorrectlyInParent()
    {
        $attributeModel = $this->getMock(
            'Magento\Catalog\Model\Resource\Eav\Attribute',
            ['getAttributeCode', '__wakeup'],
            [],
            '',
            false
        );
        $attributeModel->expects($this->once())->method('getAttributeCode')->will($this->returnValue('price1'));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $dataProviderFactory = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Filter\DataProvider\DecimalFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $instance = $objectManager->getObject(
            'Magento\Catalog\Model\Layer\Filter\Decimal',
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
