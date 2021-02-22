<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Config\Rules;

/**
 * Class SchemaLocatorTest
 *
 * Test for class \Magento\Paypal\Model\Config\Rules\SchemaLocator
 */
class SchemaLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Paypal\Model\Config\Rules\SchemaLocator
     */
    protected $schemaLocator;

    /**
     * @var \Magento\Framework\Module\Dir\Reader|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $readerMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->readerMock = $this->getMockBuilder(\Magento\Framework\Module\Dir\Reader::class)
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
