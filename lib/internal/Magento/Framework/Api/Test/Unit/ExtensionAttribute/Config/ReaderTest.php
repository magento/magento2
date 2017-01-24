<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
        $fileResolver = $this->getMockBuilder(\Magento\Framework\App\Config\FileResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $converter = $this->getMockBuilder(\Magento\Framework\Api\ExtensionAttribute\Config\Converter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $schema = $this->getMockBuilder(\Magento\Framework\Api\ExtensionAttribute\Config\SchemaLocator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $validator = $this->getMockBuilder(\Magento\Framework\Config\ValidationStateInterface::class)
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
        $this->assertInstanceOf(\Magento\Framework\Api\ExtensionAttribute\Config\Reader::class, $this->_reader);
    }
}
