<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\TestFramework\CodingStandard\Tool\CodeSniffer;

class WrapperTest extends \PHPUnit\Framework\TestCase
{
    public function testSetValues()
    {
        if (!class_exists('\PHP_CodeSniffer\Runner')) {
            $this->markTestSkipped('Code Sniffer is not installed');
        }
        $wrapper = new Wrapper();
        $expected = ['some_key' => 'some_value'];
        $wrapper->setSettings($expected);
    }
}
