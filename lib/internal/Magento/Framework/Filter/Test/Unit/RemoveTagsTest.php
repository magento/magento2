<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter\Test\Unit;

/**
 * Test for \Magento\Framework\Filter\RemoveTags
 */
class RemoveTagsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Magento\Framework\Filter\RemoveTags::filter
     * @covers \Magento\Framework\Filter\RemoveTags::_convertEntities
     */
    public function testRemoveTags()
    {
        $input = '<div>10</div> < <a>11</a> > <span>10</span>';
        $removeTags = new \Magento\Framework\Filter\RemoveTags();
        $actual = $removeTags->filter($input);
        $expected = '10 < 11 > 10';
        $this->assertSame($expected, $actual);
    }

    public function testFilterEncodedValue()
    {
        $input = '&quot;&gt;&lt;script&gt;alert(&quot;website&quot;)&lt;/script&gt;&lt;br a=&quot;';
        $removeTags = new \Magento\Framework\Filter\RemoveTags();
        $actual = $removeTags->filter($input);
        $expected = '">alert("website")';
        $this->assertSame($expected, $actual);
    }
}
