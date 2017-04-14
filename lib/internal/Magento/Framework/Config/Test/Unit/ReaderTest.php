<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Test\Unit;

use Magento\Framework\App\Config\Reader\Source\SourceInterface;
use Magento\Framework\App\Config\Scope\Converter;
use Magento\Framework\Config\Reader;
use Magento\Framework\Stdlib\ArrayUtils;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SourceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $source;

    /**
     * @var Reader
     */
    private $reader;

    public function setUp()
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
