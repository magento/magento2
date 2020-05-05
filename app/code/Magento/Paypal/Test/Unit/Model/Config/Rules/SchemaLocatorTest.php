<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Config\Rules;

use Magento\Framework\Module\Dir\Reader;
use Magento\Paypal\Model\Config\Rules\SchemaLocator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class SchemaLocatorTest
 *
 * Test for class \Magento\Paypal\Model\Config\Rules\SchemaLocator
 */
class SchemaLocatorTest extends TestCase
{
    /**
     * @var SchemaLocator
     */
    protected $schemaLocator;

    /**
     * @var Reader|MockObject
     */
    protected $readerMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->readerMock = $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->readerMock->expects($this->once())
            ->method('getModuleDir')
            ->with('etc', 'Magento_Paypal')
            ->willReturn('magento/path');

        $this->schemaLocator = new SchemaLocator($this->readerMock);
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
