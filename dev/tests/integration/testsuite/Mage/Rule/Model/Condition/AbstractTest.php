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
 * @category    Magento
 * @package     Mage_Rule
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Rule_Model_Condition_Abstract
 */
class Mage_Rule_Model_Condition_AbstractTest extends PHPUnit_Framework_TestCase
{
    public function testGetValueElement()
    {
        /** @var Mage_Rule_Model_Condition_Abstract $model */
        $model = $this->getMockForAbstractClass('Mage_Rule_Model_Condition_Abstract', array(), '',
            false, true, true, array('getValueElementRenderer'));
        $model->expects($this->any())
             ->method('getValueElementRenderer')
             ->will($this->returnValue(Mage::getObjectManager()->create('Mage_Rule_Block_Editable')));

        $rule = Mage::getObjectManager()->create('Mage_Rule_Model_Rule');
        $model->setRule($rule->setForm(Mage::getObjectManager()->create('Varien_Data_Form')));

        $property = new ReflectionProperty('Mage_Rule_Model_Condition_Abstract', '_inputType');
        $property->setAccessible(true);
        $property->setValue($model, 'date');

        $element = $model->getValueElement();
        $this->assertNotNull($element);
        $this->assertNotEmpty($element->getDateFormat());
    }
}
