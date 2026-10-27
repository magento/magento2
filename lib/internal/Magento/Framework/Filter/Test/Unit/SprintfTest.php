<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit;

use Magento\Framework\Filter\Sprintf;
use PHPUnit\Framework\TestCase;

class SprintfTest extends TestCase
{
    public function testFilter()
    {
        $sprintfFilter = new Sprintf('Formatted value: "%s"', 2, ',', ' ');
        $this->assertEquals('Formatted value: "1 234,57"', $sprintfFilter->filter(1234.56789));
    }
}
