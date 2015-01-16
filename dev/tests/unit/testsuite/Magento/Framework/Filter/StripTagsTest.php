<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

class StripTagsTest extends \PHPUnit_Framework_TestCase
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
