<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter\Test\Unit;

class SplitWordsTest extends \PHPUnit\Framework\TestCase
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
