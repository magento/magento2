<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model\Country\Postcode\Config;

use Magento\Directory\Model\Country\Postcode\Config\Data;
use Magento\Directory\Model\Country\Postcode\Config\Reader;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var Reader|MockObject
     */
    private $readerMock;

    /**
     * @var Config|MockObject
     */
    private $cacheMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->readerMock = $this->createMock(Reader::class);
        $this->cacheMock = $this->createMock(Config::class);
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
    }

    public function testGet()
    {
        $expected = ['someData' => ['someValue', 'someKey' => 'someValue']];
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn(json_encode($expected));
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($expected);
        $configData = new Data(
            $this->readerMock,
            $this->cacheMock,
            'country_postcodes',
            $this->serializerMock
        );
        $this->assertEquals($expected, $configData->get());
    }
}
