<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Config\Test\Unit;

use Magento\Framework\App\Config\Reader\Source\SourceInterface;
use Magento\Framework\Config\Reader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    /**
     * @var SourceInterface|MockObject
     */
    private $source;

    /**
     * @var Reader
     */
    private $reader;

    protected function setUp(): void
    {
        $this->source = $this->getMockBuilder(SourceInterface::class)
            ->getMockForAbstractClass();
        $this->reader = new Reader([['class' => $this->source]]);
    }

    public function testRead()
    {
        $config = [
            'default' => [
                'general/locale/code'=> 'ru_RU',
                'general/locale/timezone'=> 'America/Chicago',
            ]
        ];
        $this->source->expects($this->once())
            ->method('get')
            ->with(null)
            ->willReturn($config);
        $this->assertEquals($config, $this->reader->read());
    }
}
