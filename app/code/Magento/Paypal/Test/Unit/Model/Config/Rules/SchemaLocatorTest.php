<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Config\Rules;

/**
 * Class SchemaLocatorTest
 *
 * Test for class \Magento\Paypal\Model\Config\Rules\SchemaLocator
 */
class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Paypal\Model\Config\Rules\SchemaLocator
     */
    protected $schemaLocator;

    /**
     * @var \Magento\Framework\Module\Dir\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->readerMock = $this->getMockBuilder('Magento\Framework\Module\Dir\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $this->readerMock->expects($this->once())
            ->method('getModuleDir')
            ->with('etc', 'Magento_Paypal')
            ->willReturn('magento/path');

        $this->schemaLocator = new \Magento\Paypal\Model\Config\Rules\SchemaLocator($this->readerMock);
    }

    /**
     * Test for getSchema method
     *
     * @return void
     */
    public function testGetSchema()
    {
        $this->assertEquals('magento/path/rules.xsd', $this->schemaLocator->getSchema());
    }

    /**
     * Test for getPerFileSchema method
     *
     * @return void
     */
    public function testGetPerFileSchema()
    {
        $this->assertEquals('magento/path/rules.xsd', $this->schemaLocator->getPerFileSchema());
    }
}
