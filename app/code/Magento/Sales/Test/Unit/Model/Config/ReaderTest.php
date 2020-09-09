<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Config;

use Magento\Framework\App\Config\FileResolver;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Sales\Model\Config\Converter;
use Magento\Sales\Model\Config\Reader;
use Magento\Sales\Model\Config\SchemaLocator;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    /**
     * @var Reader
     */
    protected $_reader;

    /**
     * Prepare parameters
     */
    protected function setUp(): void
    {
        $fileResolver = $this->getMockBuilder(
            FileResolver::class
        )->disableOriginalConstructor()
            ->getMock();
        $converter = $this->getMockBuilder(
            Converter::class
        )->disableOriginalConstructor()
            ->getMock();
        $schema = $this->getMockBuilder(
            SchemaLocator::class
        )->disableOriginalConstructor()
            ->getMock();
        $validator = $this->getMockBuilder(
            ValidationStateInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->_reader = new Reader($fileResolver, $converter, $schema, $validator);
    }

    /**
     * Test creating object
     */
    public function testInstanceof()
    {
        $this->assertInstanceOf(Reader::class, $this->_reader);
    }
}
