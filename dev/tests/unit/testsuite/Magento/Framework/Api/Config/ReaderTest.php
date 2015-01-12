<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Config;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Api\Config\Reader
     */
    protected $_reader;

    /**
     * Prepare parameters
     */
    public function setUp()
    {
        $fileResolver = $this->getMockBuilder('Magento\Framework\App\Config\FileResolver')
            ->disableOriginalConstructor()
            ->getMock();
        $converter = $this->getMockBuilder('Magento\Framework\Api\Config\Converter')
            ->disableOriginalConstructor()
            ->getMock();
        $schema = $this->getMockBuilder('Magento\Framework\Api\Config\SchemaLocator')
            ->disableOriginalConstructor()
            ->getMock();
        $validator = $this->getMockBuilder('\Magento\Framework\Config\ValidationStateInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_reader = new \Magento\Framework\Api\Config\Reader($fileResolver, $converter, $schema, $validator);
    }

    /**
     * Test creating object
     */
    public function testInstanceof()
    {
        $this->assertInstanceOf('Magento\Framework\Api\Config\Reader', $this->_reader);
    }
}
