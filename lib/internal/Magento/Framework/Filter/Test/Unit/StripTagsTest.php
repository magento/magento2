<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter\Test\Unit;

class StripTagsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Magento\Framework\Filter\StripTags::filter
     */
    public function testStripTags()
    {
        $stripTags = new \Magento\Framework\Filter\StripTags(new \Magento\Framework\Escaper());
        $this->assertEquals('three', $stripTags->filter('<two>three</two>'));
    }
}
