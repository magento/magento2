<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Filter;

class SprintfTest extends \PHPUnit_Framework_TestCase
{
    public function testFilter()
    {
        $sprintfFilter = new \Magento\Framework\Filter\Sprintf('Formatted value: "%s"', 2, ',', ' ');
        $this->assertEquals('Formatted value: "1 234,57"', $sprintfFilter->filter(1234.56789));
    }
}
