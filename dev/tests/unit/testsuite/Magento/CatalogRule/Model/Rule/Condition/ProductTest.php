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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\CatalogRule\Model\Rule\Condition;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CatalogRule\Model\Rule\Condition\Product */
    protected $product;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject */
    protected $productModel;

    /** @var \Magento\Catalog\Model\Resource\Product|\PHPUnit_Framework_MockObject_MockObject */
    protected $productResource;

    /** @var \Magento\Catalog\Model\Resource\Eav\Attribute|\PHPUnit_Framework_MockObject_MockObject */
    protected $eavAttributeResource;

    protected function setUp()
    {
        $this->config = $this->getMock('Magento\Eav\Model\Config', array('getAttribute'), array(), '', false);
        $this->productModel = $this->getMock(
            'Magento\Catalog\Model\Product',
            array(
                '__wakeup',
                'getAvailableInCategories',
                'hasData',
                'getData',
                'getId',
                'getStoreId',
                'getResource'
            ),
            array(),
            '',
            false
        );
        $this->productResource = $this->getMock(
            'Magento\Catalog\Model\Resource\Product',
            ['loadAllAttributes',
                'getAttributesByCode',
                'getAttribute'
            ],
            array(),
            '',
            false
        );
        $this->eavAttributeResource = $this->getMock(
            '\Magento\Catalog\Model\Resource\Eav\Attribute',
            array(
                '__wakeup',
                'isAllowedForRuleCondition',
                'getDataUsingMethod',
                'getAttributeCode',
                'getFrontendLabel',
                'isScopeGlobal',
                'getBackendType',
                'getFrontendInput'
            ),
            array(),
            '',
            false
        );

        $this->productResource->expects($this->any())->method('loadAllAttributes')
            ->will($this->returnSelf());
        $this->productResource->expects($this->any())->method('getAttributesByCode')
            ->will($this->returnValue(array($this->eavAttributeResource)));
        $this->eavAttributeResource->expects($this->any())->method('isAllowedForRuleCondition')
            ->will($this->returnValue(false));
        $this->eavAttributeResource->expects($this->any())->method('getAttributesByCode')
            ->will($this->returnValue(false));
        $this->eavAttributeResource->expects($this->any())->method('getAttributeCode')
            ->will($this->returnValue('1'));
        $this->eavAttributeResource->expects($this->any())->method('getFrontendLabel')
            ->will($this->returnValue('attribute_label'));

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->product = $this->objectManagerHelper->getObject(
            'Magento\CatalogRule\Model\Rule\Condition\Product',
            array(
                'config' => $this->config,
                'product' => $this->productModel,
                'productResource' => $this->productResource
            )
        );
    }

    public function testValidateMeetsCategory()
    {
        $this->product->setData('attribute', 'category_ids');
        $this->product->setData('value_parsed', '1');
        $this->product->setData('operator', '>=');

        $this->productModel->expects($this->once())->method('getAvailableInCategories')
            ->will($this->returnValue('2'));
        $this->assertTrue($this->product->validate($this->productModel));
    }

    /**
     * @dataProvider validateDataProvider
     *
     * @param string $attributeValue
     * @param string|array $parsedValue
     * @param string $newValue
     * @param string $operator
     * @param array $input
     */
    public function testValidateWithDatetimeValue($attributeValue, $parsedValue, $newValue, $operator, $input)
    {
        $this->product->setData('attribute', 'attribute_key');
        $this->product->setData('value_parsed', $parsedValue);
        $this->product->setData('operator', $operator);

        $this->config->expects($this->any())->method('getAttribute')
            ->will($this->returnValue($this->eavAttributeResource));

        $this->eavAttributeResource->expects($this->any())->method('isScopeGlobal')
            ->will($this->returnValue(false));
        $this->eavAttributeResource->expects($this->any())->method($input['method'])
            ->will($this->returnValue($input['type']));

        $this->productModel->expects($this->any())->method('hasData')
            ->will($this->returnValue(true));
        $this->productModel->expects($this->at(0))->method('getData')
            ->will($this->returnValue(array ('1' => array('1' => $attributeValue))));
        $this->productModel->expects($this->any())->method('getData')
            ->will($this->returnValue($newValue));
        $this->productModel->expects($this->any())->method('getId')
            ->will($this->returnValue('1'));
        $this->productModel->expects($this->once())->method('getStoreId')
            ->will($this->returnValue('1'));
        $this->productModel->expects($this->any())->method('getResource')
            ->will($this->returnValue($this->productResource));

        $this->productResource->expects($this->any())->method('getAttribute')
            ->will($this->returnValue($this->eavAttributeResource));

        $this->product->collectValidatedAttributes($this->productModel);
        $this->assertTrue($this->product->validate($this->productModel));
    }

    public function validateDataProvider()
    {
        return array(
            array(
                'attribute_value' => '12:12',
                'parsed_value' => '12:12',
                'new_value' => '12:13',
                'operator' => '>=',
                'input' => array('method' => 'getBackendType', 'type' => 'input_type')
            ),
            array(
                'attribute_value' => '1',
                'parsed_value' => '1',
                'new_value' => '2',
                'operator' => '>=',
                'input' => array('method' => 'getBackendType', 'type' => 'input_type')
            ),
            array(
                'attribute_value' => '1',
                'parsed_value' => array('1' => '0'),
                'new_value' => array('1' => '1'),
                'operator' => '!()',
                'input' => array('method' => 'getFrontendInput', 'type' => 'multiselect')
            )
        );
    }

}
