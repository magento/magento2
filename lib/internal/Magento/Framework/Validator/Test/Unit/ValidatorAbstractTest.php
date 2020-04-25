<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator\Test\Unit;

use Magento\Framework\Translate\AbstractAdapter;
use Magento\Framework\Translate\AdapterInterface;
use Magento\Framework\Validator\AbstractValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test case for \Magento\Framework\Validator\AbstractValidator
 */
class ValidatorAbstractTest extends TestCase
{
    /**
     * @var null|AdapterInterface
     */
    protected $_defaultTranslator = null;

    protected function setUp(): void
    {
        $this->_defaultTranslator = AbstractValidator::getDefaultTranslator();
    }

    protected function tearDown(): void
    {
        AbstractValidator::setDefaultTranslator($this->_defaultTranslator);
    }

    /**
     * Get translator object
     *
     * @return MockObject|AbstractAdapter
     */
    protected function _getTranslator()
    {
        return $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();
    }

    /**
     * Test default translator get/set
     */
    public function testDefaultTranslatorGetSet()
    {
        $translator = $this->_getTranslator();
        AbstractValidator::setDefaultTranslator($translator);
        $this->assertEquals($translator, AbstractValidator::getDefaultTranslator());
    }

    /**
     * Test get/set/has translator
     */
    public function testTranslatorGetSetHas()
    {
        /** @var \Magento\Framework\Validator\AbstractValidator $validator */
        $validator = $this->getMockBuilder(
            AbstractValidator::class
        )->getMockForAbstractClass();
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
        $validator = $this->getMockBuilder(
            AbstractValidator::class
        )->getMockForAbstractClass();
        $translator = $this->_getTranslator();
        AbstractValidator::setDefaultTranslator($translator);
        $this->assertEquals($translator, $validator->getTranslator());
        $this->assertFalse($validator->hasTranslator());
    }
}
