<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Model\Config\Reader;

class XmlTest extends \PHPUnit_Framework_TestCase
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
            'Magento\Framework\App\Config\FileResolver'
        )->disableOriginalConstructor()->getMock();
        $converter = $this->getMockBuilder(
            'Magento\Cron\Model\Config\Converter\Xml'
        )->disableOriginalConstructor()->getMock();
        $schema = $this->getMockBuilder(
            'Magento\Cron\Model\Config\SchemaLocator'
        )->disableOriginalConstructor()->getMock();
        $validator = $this->getMockBuilder(
            '\Magento\Framework\Config\ValidationStateInterface'
        )->disableOriginalConstructor()->getMock();
        $this->_xmlReader = new \Magento\Cron\Model\Config\Reader\Xml($fileResolver, $converter, $schema, $validator);
    }

    /**
     * Test creating object
     */
    public function testInstanceof()
    {
        $this->assertInstanceOf('Magento\Cron\Model\Config\Reader\Xml', $this->_xmlReader);
    }
}
