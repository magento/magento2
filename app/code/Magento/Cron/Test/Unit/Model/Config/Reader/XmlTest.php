<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cron\Test\Unit\Model\Config\Reader;

use Magento\Cron\Model\Config\Reader\Xml;
use Magento\Cron\Model\Config\SchemaLocator;
use Magento\Framework\App\Config\FileResolver;
use Magento\Framework\Config\ValidationStateInterface;
use PHPUnit\Framework\TestCase;

class XmlTest extends TestCase
{
    /**
     * @var Xml
     */
    protected $_xmlReader;

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
            \Magento\Cron\Model\Config\Converter\Xml::class
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
        $this->_xmlReader = new Xml($fileResolver, $converter, $schema, $validator);
    }

    /**
     * Test creating object
     */
    public function testInstanceof()
    {
        $this->assertInstanceOf(Xml::class, $this->_xmlReader);
    }
}
