<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\Framework\Filesystem\Test\Unit\File;

use Magento\Framework\Filesystem\Filter\ExcludeFilter;
use PHPUnit\Framework\TestCase;

class ExcludeFilterTest extends TestCase
{
    /**
     * @var \Iterator
     */
    protected $iterator;

    protected function setUp(): void
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

        $result = [];
        foreach ($iterator as $i) {
            $result[] = $i;
        }

        $this->assertNotContains(BP . '/var/session/', $result, 'Filtered path should not be in array');
    }

    /**
     * @return \Generator
     */
    private function getFilesIterator()
    {
        $files = [
            BP . '/var/',
            BP . '/var/session/',
            BP . '/var/cache/'
        ];

        foreach ($files as $file) {
            $item = $this->getMockBuilder(
                \SplFileInfo::class
            )->disableOriginalConstructor()->setMethods(['__toString', 'getFilename'])->getMock();
            $item->expects($this->any())->method('__toString')->willReturn($file);
            $item->expects($this->any())->method('getFilename')->willReturn('notDots');
            yield $item;
        }
    }
}
