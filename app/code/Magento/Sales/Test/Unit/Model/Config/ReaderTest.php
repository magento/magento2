<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Config;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Config\Reader
     */
    protected $_reader;

    /**
     * Prepare parameters
     */
    public function setUp()
    {
        $fileResolver = $this->getMockBuilder(
            'Magento\Framework\App\Config\FileResolver'
        )->disableOriginalConstructor()->getMock();
        $converter = $this->getMockBuilder(
            'Magento\Sales\Model\Config\Converter'
        )->disableOriginalConstructor()->getMock();
        $schema = $this->getMockBuilder(
            'Magento\Sales\Model\Config\SchemaLocator'
        )->disableOriginalConstructor()->getMock();
        $validator = $this->getMockBuilder(
            '\Magento\Framework\Config\ValidationStateInterface'
        )->disableOriginalConstructor()->getMock();
        $this->_reader = new \Magento\Sales\Model\Config\Reader($fileResolver, $converter, $schema, $validator);
    }

    /**
     * Test creating object
     */
    public function testInstanceof()
    {
        $this->assertInstanceOf('Magento\Sales\Model\Config\Reader', $this->_reader);
    }
}
