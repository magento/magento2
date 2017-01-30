<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Filesystem\Test\Unit\File;

use \Magento\Framework\Filesystem\Filter\ExcludeFilter;

/**
 * Class ExcludeFilterTest
 */
class ExcludeFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Iterator
     */
    protected $iterator;

    protected function setUp()
    {
        $this->iterator = $this->getFilesIterator();
    }

    public function testExclusion()
    {
        $iterator = new ExcludeFilter(
            $this->iterator,
            [
                BP . '/var/session/'
            ]
        );

        foreach ($iterator as $i) {
            $result[] = $i;
        }

        $this->assertTrue(!in_array(BP . '/var/session/', $result), 'Filtered path should not be in array');
    }

    private function getFilesIterator ()
    {
        $files = [
            BP . '/var/',
            BP . '/var/session/',
            BP . '/var/cache/'
        ];

        foreach ($files as $file) {
            $item = $this->getMockBuilder('SplFileInfoClass')->setMethods(['__toString', 'getFilename'])->getMock();
            $item->expects($this->any())->method('__toString')->willReturn($file);
            $item->expects($this->any())->method('getFilename')->willReturn('notDots');
            yield $item;
        }
    }
}
