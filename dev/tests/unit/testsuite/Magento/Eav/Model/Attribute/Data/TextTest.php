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
namespace Magento\Eav\Model\Attribute\Data;

class TextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Attribute\Data\Text
     */
    protected $_model;

    protected function setUp()
    {
        $locale = $this->getMock(
            'Magento\Framework\Stdlib\DateTime\TimezoneInterface',
            array(),
            array(),
            '',
            false,
            false
        );
        $localeResolver = $this->getMock(
            'Magento\Framework\Locale\ResolverInterface',
            array(),
            array(),
            '',
            false,
            false
        );
        $logger = $this->getMock('Magento\Framework\Logger', array(), array(), '', false, false);
        $helper = $this->getMock('Magento\Framework\Stdlib\String', array(), array(), '', false, false);

        $attributeData = array(
            'store_label' => 'Test',
            'attribute_code' => 'test',
            'is_required' => 1,
            'validate_rules' => array('min_text_length' => 0, 'max_text_length' => 0, 'input_validation' => 0)
        );

        $attributeClass = 'Magento\Eav\Model\Entity\Attribute\AbstractAttribute';
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $eavTypeFactory = $this->getMock('Magento\Eav\Model\Entity\TypeFactory', array(), array(), '', false, false);
        $arguments = $objectManagerHelper->getConstructArguments(
            $attributeClass,
            array('eavTypeFactory' => $eavTypeFactory, 'data' => $attributeData)
        );

        /** @var $attribute \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|
         * \PHPUnit_Framework_MockObject_MockObject
         */
        $attribute = $this->getMock($attributeClass, array('_init'), $arguments);
        $this->_model = new \Magento\Eav\Model\Attribute\Data\Text($locale, $logger, $localeResolver, $helper);
        $this->_model->setAttribute($attribute);
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    public function testValidateValueString()
    {
        $inputValue = '0';
        $expectedResult = true;
        $this->assertEquals($expectedResult, $this->_model->validateValue($inputValue));
    }

    public function testValidateValueInteger()
    {
        $inputValue = 0;
        $expectedResult = array('"Test" is a required value.');
        $result = $this->_model->validateValue($inputValue);
        $this->assertEquals($expectedResult, array((string)$result[0]));
    }
}
