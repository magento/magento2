<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit;

use Magento\Framework\Filter\RemoveTags;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Framework\Filter\RemoveTags
 */
class RemoveTagsTest extends TestCase
{
    /**
     * @covers \Magento\Framework\Filter\RemoveTags::filter
     * @covers \Magento\Framework\Filter\RemoveTags::_convertEntities
     */
    public function testRemoveTags()
    {
        $input = '<div>10</div> < <a>11</a> > <span>10</span>';
        $removeTags = new RemoveTags();
        $actual = $removeTags->filter($input);
        $expected = '10 < 11 > 10';
        $this->assertSame($expected, $actual);
    }

    public function testFilterEncodedValue()
    {
        $input = '&quot;&gt;&lt;script&gt;alert(&quot;website&quot;)&lt;/script&gt;&lt;br a=&quot;';
        $removeTags = new RemoveTags();
        $actual = $removeTags->filter($input);
        $expected = '">alert("website")';
        $this->assertSame($expected, $actual);
    }
}
