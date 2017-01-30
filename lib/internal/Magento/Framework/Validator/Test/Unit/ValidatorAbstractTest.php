<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Validator\Test\Unit;

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
