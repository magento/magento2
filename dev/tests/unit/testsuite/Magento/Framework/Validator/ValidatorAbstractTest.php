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

namespace Magento\Framework\Validator;

/**
 * Test case for \Magento\Framework\Validator\AbstractValidator
 */
class ValidatorAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var null|\Magento\Framework\Translate\AdapterInterface
     */
    protected $_defaultTranslator = null;

    protected function setUp()
    {
        $this->_defaultTranslator = \Magento\Framework\Validator\AbstractValidator::getDefaultTranslator();
    }

    protected function tearDown()
    {
        \Magento\Framework\Validator\AbstractValidator::setDefaultTranslator($this->_defaultTranslator);
    }

    /**
     * Get translator object
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Translate\AbstractAdapter
     */
    protected function _getTranslator()
    {
        return $this->getMockBuilder('Magento\Framework\Translate\AdapterInterface')->getMockForAbstractClass();
    }

    /**
     * Test default translator get/set
     */
    public function testDefaultTranslatorGetSet()
    {
        $translator = $this->_getTranslator();
        \Magento\Framework\Validator\AbstractValidator::setDefaultTranslator($translator);
        $this->assertEquals($translator, \Magento\Framework\Validator\AbstractValidator::getDefaultTranslator());
    }

    /**
     * Test get/set/has translator
     */
    public function testTranslatorGetSetHas()
    {
        /** @var \Magento\Framework\Validator\AbstractValidator $validator */
        $validator = $this->getMockBuilder('Magento\Framework\Validator\AbstractValidator')->getMockForAbstractClass();
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
        /** @var \Magento\Framework\Validator\AbstractValidator $validator */
        $validator = $this->getMockBuilder('Magento\Framework\Validator\AbstractValidator')->getMockForAbstractClass();
        $translator = $this->_getTranslator();
        \Magento\Framework\Validator\AbstractValidator::setDefaultTranslator($translator);
        $this->assertEquals($translator, $validator->getTranslator());
        $this->assertFalse($validator->hasTranslator());
    }
}
