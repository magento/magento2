<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Translate\Test\Unit;

class AdapterAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Translate\AbstractAdapter
     */
    protected $_model = null;

    protected function setUp()
    {
        $this->_model = $this->getMockBuilder(\Magento\Framework\Translate\AbstractAdapter::class)
            ->getMockForAbstractClass();
    }

    /**
     * Magento translate adapter should always return false to be used correctly be Zend Validate
     */
    public function testIsTranslated()
    {
        $this->assertFalse($this->_model->isTranslated('string'));
    }

    /**
     * Test set locale do nothing
     */
    public function testSetLocale()
    {
        $this->assertInstanceOf(
            \Magento\Framework\Translate\AbstractAdapter::class,
            $this->_model->setLocale('en_US')
        );
    }

    /**
     * Check that abstract method is implemented
     */
    public function testToString()
    {
        $this->assertEquals(\Magento\Framework\Translate\Adapter::class, $this->_model->toString());
    }
}
