<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model\Attribute;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ConditionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider convertAttributeDataProvider
     *
     * @param $productAttributeValue
     * @param $attributeFrontendType
     * @param $conditionValue
     */
    public function testConvertAttribute($productAttributeValue, $attributeFrontendType, $conditionValue)
    {
        $googleAttributeId = 1;
        $conditionType = \Magento\GoogleShopping\Model\Attribute\Condition::ATTRIBUTE_TYPE_TEXT;
        $conditionName = 'condition';
        $googleShoppingAttribute = $this->getMock(
            'Magento\Framework\Gdata\Gshopping\Extension\Attribute',
            null,
            [$conditionName]
        );
        $googleShoppingEntry = $this->getMock(
            'Magento\Framework\Gdata\Gshopping\Entry',
            ['getContentAttributeByName'],
            [],
            '',
            false
        );
        $googleShoppingEntry->expects($this->any())
            ->method('getContentAttributeByName')
            ->with($conditionName)
            ->will($this->returnValue($googleShoppingAttribute));
        $product = $this->getMock('Magento\Catalog\Model\Product', ['__wakeup', 'getData'], [], '', false);
        $product->expects($this->any())
            ->method('getData')
            ->with($conditionName)
            ->will($this->returnValue($productAttributeValue));
        $attributeFrontend = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend',
            ['getValue'],
            [],
            '',
            false
        );
        $attributeFrontend->expects($this->any())
            ->method('getValue')
            ->with($product)
            ->will($this->returnValue($productAttributeValue));
        $catalogEntityAttribute = $this->getMock(
            'Magento\Catalog\Model\Entity\Attribute',
            ['__wakeup', 'getFrontend', 'getFrontendInput', 'getAttributeCode'],
            [],
            '',
            false
        );
        $catalogEntityAttribute->expects($this->any())
            ->method('getFrontend')
            ->will($this->returnValue($attributeFrontend));
        $catalogEntityAttribute->expects($this->any())
            ->method('getFrontendInput')
            ->will($this->returnValue($attributeFrontendType));
        $catalogEntityAttribute->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue($conditionName));
        $googleShoppingProduct = $this->getMock(
            'Magento\GoogleShopping\Helper\Product',
            ['getProductAttribute'],
            [],
            '',
            false
        );
        $googleShoppingProduct->expects($this->any())
            ->method('getProductAttribute')
            ->with($product, $googleAttributeId)
            ->will($this->returnValue($catalogEntityAttribute));

        $model = (new ObjectManagerHelper($this))->getObject(
            'Magento\GoogleShopping\Model\Attribute\Condition',
            [
                'gsProduct' => $googleShoppingProduct
            ]
        );
        $model->setAttributeId($googleAttributeId);
        $this->assertEquals($googleShoppingEntry, $model->convertAttribute($product, $googleShoppingEntry));
        $this->assertEquals(
            [
                $conditionValue,
                $conditionType,
                $conditionName,
            ],
            [
                $googleShoppingAttribute->getText(),
                $googleShoppingAttribute->getType(),
                $googleShoppingAttribute->getName()
            ]
        );
    }

    public function convertAttributeDataProvider()
    {
        return [
            ['Used', 'text', \Magento\GoogleShopping\Model\Attribute\Condition::CONDITION_USED],
            ['2014-10-25T15:14:13', 'date', \Magento\GoogleShopping\Model\Attribute\Condition::CONDITION_NEW],
        ];
    }
}
