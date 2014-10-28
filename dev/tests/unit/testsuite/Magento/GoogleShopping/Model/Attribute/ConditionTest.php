<?php
/**
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
                $conditionName
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
