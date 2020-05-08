<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model\Country\Postcode;

use Magento\Directory\Model\Country\Postcode\Config;
use Magento\Directory\Model\Country\Postcode\Config\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $dataStorageMock;

    protected function setUp(): void
    {
        $this->dataStorageMock = $this->createMock(Data::class);
    }

    public function testGet()
    {
        $expected = ['US' => ['pattern_01' => 'pattern_01', 'pattern_02' => 'pattern_02']];
        $this->dataStorageMock->expects($this->once())->method('get')->willReturn($expected);
        $configData = new Config($this->dataStorageMock);
        $this->assertEquals($expected, $configData->getPostCodes());
    }
}
