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
namespace Magento\Tax\Model\Resource\Calculation\Rule;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Test setClassTypeFilter with correct Class Type
     *
     * @param $classType
     * @param $elementId
     * @param $expected
     *
     * @dataProvider setClassTypeFilterDataProvider
     */
    public function testSetClassTypeFilter($classType, $elementId, $expected)
    {
        $collection = $this->_objectManager->create('Magento\Tax\Model\Resource\Calculation\Rule\Collection');
        $collection->setClassTypeFilter($classType, $elementId);
        $this->assertRegExp($expected, (string)$collection->getSelect());
    }

    public function setClassTypeFilterDataProvider()
    {
        return array(
            array(
                \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT,
                1,
                '/`?cd`?\.`?product_tax_class_id`? = [\S]{0,1}1[\S]{0,1}/'
            ),
            array(
                \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER,
                1,
                '/`?cd`?\.`?customer_tax_class_id`? = [\S]{0,1}1[\S]{0,1}/'
            )
        );
    }

    /**
     * Test setClassTypeFilter with wrong Class Type
     *
     * @expectedException \Magento\Framework\Model\Exception
     */
    public function testSetClassTypeFilterWithWrongType()
    {
        $collection = $this->_objectManager->create('Magento\Tax\Model\Resource\Calculation\Rule\Collection');
        $collection->setClassTypeFilter('WrongType', 1);
    }
}
