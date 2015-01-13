<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

class SplitWordsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Bug: $maxWordLength parameter has a misleading name. It limits qty of words in the result.
     */
    public function testSplitWords()
    {
        $words = '123  123  45 789';
        $filter = new \Magento\Framework\Filter\SplitWords(false, 3);
        $this->assertEquals(['123', '123', '45'], $filter->filter($words));
        $filter = new \Magento\Framework\Filter\SplitWords(true, 2);
        $this->assertEquals(['123', '45'], $filter->filter($words));
    }
}
