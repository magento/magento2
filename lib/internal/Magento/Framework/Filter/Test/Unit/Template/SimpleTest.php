<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit\Template;

use Magento\Framework\Filter\Template\Simple;
use PHPUnit\Framework\TestCase;

class SimpleTest extends TestCase
{
    /**
     * @var Simple
     */
    protected $_filter;

    protected function setUp(): void
    {
        $this->_filter = new Simple();
    }

    public function testFilter()
    {
        $template = 'My name is "{{first name}}" and my date of birth is {{dob}}.';
        $values = ['first name' => 'User', 'dob' => 'Feb 29, 2000'];
        $this->_filter->setData($values);
        $actual = $this->_filter->filter($template);
        $expected = 'My name is "User" and my date of birth is Feb 29, 2000.';
        $this->assertSame($expected, $actual);
    }

    /**
     * @param string $startTag
     * @param string $endTag
     * @dataProvider setTagsDataProvider
     */
    public function testSetTags($startTag, $endTag)
    {
        $this->_filter->setTags($startTag, $endTag);
        $this->_filter->setData(['pi' => '3.14']);
        $template = "PI = {$startTag}pi{$endTag}";
        $actual = $this->_filter->filter($template);
        $expected = 'PI = 3.14';
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array
     */
    public function setTagsDataProvider()
    {
        return ['(brackets)' => ['(', ')'], '#hash#' => ['#', '#']];
    }
}
