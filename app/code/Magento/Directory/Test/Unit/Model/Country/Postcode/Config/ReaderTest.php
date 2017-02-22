<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Test\Unit\Model\Country\Postcode\Config;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Config\Reader
     */
    protected $reader;

    /**
     * Prepare parameters
     */
    public function setUp()
    {
        $fileResolver = $this->getMockBuilder(
            'Magento\Framework\App\Config\FileResolver'
        )->disableOriginalConstructor()->getMock();
        $converter = $this->getMockBuilder(
            'Magento\Directory\Model\Country\Postcode\Config\Converter'
        )->disableOriginalConstructor()->getMock();
        $schema = $this->getMockBuilder(
            'Magento\Directory\Model\Country\Postcode\Config\SchemaLocator'
        )->disableOriginalConstructor()->getMock();
        $validator = $this->getMockBuilder(
            'Magento\Framework\Config\ValidationStateInterface'
        )->disableOriginalConstructor()->getMock();
        $this->reader = new \Magento\Directory\Model\Country\Postcode\Config\Reader(
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
        $this->assertInstanceOf('Magento\Directory\Model\Country\Postcode\Config\Reader', $this->reader);
    }
}
