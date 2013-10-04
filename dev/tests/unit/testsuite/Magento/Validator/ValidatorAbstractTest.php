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
 * @package     \Magento\Validator
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test case for \Magento\Validator\AbstractValidator
 */
namespace Magento\Validator;

class ValidatorAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var null|\Magento\Translate\AdapterInterface
     */
    protected $_defaultTranslator = null;

    protected function setUp()
    {
        $this->_defaultTranslator = \Magento\Validator\AbstractValidator::getDefaultTranslator();
    }

    protected function tearDown()
    {
        \Magento\Validator\AbstractValidator::setDefaultTranslator($this->_defaultTranslator);
    }

    /**
     * Get translator object
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Translate\AbstractAdapter
     */
    protected function _getTranslator()
    {
        return $this->getMockBuilder('Magento\Translate\AdapterInterface')
            ->getMockForAbstractClass();
    }

    /**
     * Test default translator get/set
     */
    public function testDefaultTranslatorGetSet()
    {
        $translator = $this->_getTranslator();
        \Magento\Validator\AbstractValidator::setDefaultTranslator($translator);
        $this->assertEquals($translator, \Magento\Validator\AbstractValidator::getDefaultTranslator());
    }

    /**
     * Test get/set/has translator
     */
    public function testTranslatorGetSetHas()
    {
        /** @var \Magento\Validator\AbstractValidator $validator */
        $validator = $this->getMockBuilder('Magento\Validator\AbstractValidator')
            ->getMockForAbstractClass();
        $translator = $this->_getTranslator();
        $validator->setTranslator($translator);
        $this->assertEquals($translator, $validator->getTranslator());
        $this->assertTrue($validator->hasTranslator());
    }

    /**
     * Check that default translator returned if set and no translator set
     */
    public function testGetTranslatorDefault()
    {
        /** @var \Magento\Validator\AbstractValidator $validator */
        $validator = $this->getMockBuilder('Magento\Validator\AbstractValidator')
            ->getMockForAbstractClass();
        $translator = $this->_getTranslator();
        \Magento\Validator\AbstractValidator::setDefaultTranslator($translator);
        $this->assertEquals($translator, $validator->getTranslator());
        $this->assertFalse($validator->hasTranslator());
    }
}
