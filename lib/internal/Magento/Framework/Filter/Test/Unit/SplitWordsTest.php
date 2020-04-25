<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit;

use Magento\Framework\Filter\SplitWords;
use PHPUnit\Framework\TestCase;

class SplitWordsTest extends TestCase
{
    /**
     * Bug: $maxWordLength parameter has a misleading name. It limits qty of words in the result.
     */
    public function testSplitWords()
    {
        $words = '123  123  45 789';
        $filter = new SplitWords(false, 3);
        $this->assertEquals(['123', '123', '45'], $filter->filter($words));
        $filter = new SplitWords(true, 2);
        $this->assertEquals(['123', '45'], $filter->filter($words));
    }
}
