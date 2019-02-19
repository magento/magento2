<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Model\Config\Reader;

class XmlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Cron\Model\Config\Reader\Xml
     */
    protected $_xmlReader;

    /**
     * Prepare parameters
     */
    protected function setUp()
    {
        $fileResolver = $this->getMockBuilder(
            \Magento\Framework\App\Config\FileResolver::class
        )->disableOriginalConstructor()->getMock();
        $converter = $this->getMockBuilder(
            \Magento\Cron\Model\Config\Converter\Xml::class
        )->disableOriginalConstructor()->getMock();
        $schema = $this->getMockBuilder(
            \Magento\Cron\Model\Config\SchemaLocator::class
        )->disableOriginalConstructor()->getMock();
        $validator = $this->getMockBuilder(
            \Magento\Framework\Config\ValidationStateInterface::class
        )->disableOriginalConstructor()->getMock();
        $this->_xmlReader = new \Magento\Cron\Model\Config\Reader\Xml($fileResolver, $converter, $schema, $validator);
    }

    /**
     * Test creating object
     */
    public function testInstanceof()
    {
        $this->assertInstanceOf(\Magento\Cron\Model\Config\Reader\Xml::class, $this->_xmlReader);
    }
}
