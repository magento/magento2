<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Translate\Test\Unit;

use Magento\Framework\Translate\AbstractAdapter;
use Magento\Framework\Translate\Adapter;
use PHPUnit\Framework\TestCase;

class AdapterAbstractTest extends TestCase
{
    /**
     * @var AbstractAdapter
     */
    protected $_model = null;

    protected function setUp(): void
    {
        $this->_model = $this->getMockBuilder(AbstractAdapter::class)
            ->onlyMethods(['translate', '__'])
            ->getMock();
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
            AbstractAdapter::class,
            $this->_model->setLocale('en_US')
        );
    }

    /**
     * Check that abstract method is implemented
     */
    public function testToString()
    {
        $this->assertEquals(Adapter::class, $this->_model->toString());
    }
}
