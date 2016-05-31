<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Test\Unit\ExtensionAttribute\Config;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\Config\Reader
     */
    protected $_reader;

    /**
     * Prepare parameters
     */
    protected function setUp()
    {
        $fileResolver = $this->getMockBuilder('Magento\Framework\App\Config\FileResolver')
            ->disableOriginalConstructor()
            ->getMock();
        $converter = $this->getMockBuilder('Magento\Framework\Api\ExtensionAttribute\Config\Converter')
            ->disableOriginalConstructor()
            ->getMock();
        $schema = $this->getMockBuilder('Magento\Framework\Api\ExtensionAttribute\Config\SchemaLocator')
            ->disableOriginalConstructor()
            ->getMock();
        $validator = $this->getMockBuilder('\Magento\Framework\Config\ValidationStateInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_reader = new \Magento\Framework\Api\ExtensionAttribute\Config\Reader(
            $fileResolver,
            $converter,
            $schema,
            $validator
        );
    }

    /**
     * Test creating object
     */
    public function testInstanceof()
    {
        $this->assertInstanceOf('Magento\Framework\Api\ExtensionAttribute\Config\Reader', $this->_reader);
    }
}
