<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Xml\Test\Unit;

use Magento\Framework\Xml\Security;

/**
 * Class SecurityTest
 *
 * Test for class \Magento\Framework\Xml\Security
 */
class SecurityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Security
     */
    protected $security;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->security = new Security();
    }

    /**
     * Run test scan method
     *
     * @param string $xmlContent
     * @param bool $expectedResult
     *
     * @dataProvider dataProviderTestScan
     */
    public function testScan($xmlContent, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->security->scan($xmlContent));
    }

    /**
     * Data provider for testScan
     *
     * @return array
     */
    public function dataProviderTestScan()
    {
        return [
            [
                'xmlContent' => '<?xml version="1.0"?><test></test>',
                'expectedResult' => true
            ],
            [
                'xmlContent' => '<!DOCTYPE note SYSTEM "Note.dtd"><?xml version="1.0"?><test></test>',
                'expectedResult' => false
            ],
            [
                'xmlContent' => '<?xml version="1.0"?>
            <!DOCTYPE test [
              <!ENTITY value "value">
              <!ENTITY value1 "&value;&value;&value;&value;&value;&value;&value;&value;&value;&value;">
              <!ENTITY value2 "&value1;&value1;&value1;&value1;&value1;&value1;&value1;&value1;&value1;&value1;">
            ]>
            <test>&value2;</test>',
                'expectedResult' => false
            ],
            [
                'xmlContent' => '<!DOCTYPE html><?xml version="1.0"?><test></test>',
                'expectedResult' => false
            ],
            [
                'xmlContent' => '',
                'expectedResult' => false
            ]
        ];
    }
}
