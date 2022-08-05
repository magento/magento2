<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Model\DeploymentConfig;

use Magento\Deploy\Model\DeploymentConfig\DataCollector;
use Magento\Deploy\Model\DeploymentConfig\Hash;
use Magento\Deploy\Model\DeploymentConfig\Hash\Generator;
use Magento\Framework\Flag;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\FlagFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HashTest extends TestCase
{
    /**
     * @var Generator|MockObject
     */
    private $configHashGeneratorMock;

    /**
     * @var DataCollector|MockObject
     */
    private $dataConfigCollectorMock;

    /**
     * @var FlagFactory|MockObject
     */
    private $flagFactoryMock;

    /**
     * @var FlagResource|MockObject
     */
    private $flagResourceMock;

    /**
     * @var Flag|MockObject
     */
    private $flagMock;

    /**
     * @var Hash
     */
    private $hash;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->flagResourceMock = $this->getMockBuilder(FlagResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->flagFactoryMock = $this->getMockBuilder(FlagFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->flagMock = $this->getMockBuilder(Flag::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configHashGeneratorMock = $this->getMockBuilder(Generator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataConfigCollectorMock = $this->getMockBuilder(DataCollector::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->hash = new Hash(
            $this->configHashGeneratorMock,
            $this->dataConfigCollectorMock,
            $this->flagResourceMock,
            $this->flagFactoryMock
        );
    }

    /**
     * @param string|array|null $dataFromStorage
     * @param array $expectedResult
     * @return void
     * @dataProvider getDataProvider
     */
    public function testGet($dataFromStorage, $expectedResult)
    {
        $this->flagMock->expects($this->once())
            ->method('getFlagData')
            ->willReturn($dataFromStorage);
        $this->flagFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => ['flag_code' => Hash::CONFIG_KEY]])
            ->willReturn($this->flagMock);
        $this->flagResourceMock->expects($this->once())
            ->method('load')
            ->with($this->flagMock, Hash::CONFIG_KEY, 'flag_code')
            ->willReturnSelf();

        $this->assertSame($expectedResult, $this->hash->get());
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        return [
            [['section' => 'hash'], ['section' => 'hash']],
            ['hash', ['hash']],
            ['', []],
            [null, []],
        ];
    }

    /**
     * @return void
     */
    public function testRegenerate()
    {
        $section = 'section';
        $config = 'some config';
        $fullConfig = ['section' => $config];
        $hash = 'some hash';
        $hashes = [$section => $hash];

        $this->generalRegenerateMocks($fullConfig, $config, $hash, $hashes);

        $this->flagResourceMock->expects($this->once())
            ->method('save')
            ->with($this->flagMock)
            ->willReturnSelf();

        $this->hash->regenerate();
    }

    /**
     * @return void
     */
    public function testRegenerateWithException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('The hash isn\'t saved.');
        $section = 'section';
        $config = 'some config';
        $fullConfig = ['section' => $config];
        $hash = 'some hash';
        $hashes = [$section => $hash];

        $this->generalRegenerateMocks($fullConfig, $config, $hash, $hashes, $section);

        $this->flagResourceMock->expects($this->once())
            ->method('save')
            ->with($this->flagMock)
            ->willThrowException(new \Exception('Some error'));
        $this->hash->regenerate($section);
    }

    /**
     * @param array $fullConfig
     * @param string $config
     * @param string $hash
     * @param array $hashes
     * @param string|null $sectionName
     * @return void
     */
    private function generalRegenerateMocks($fullConfig, $config, $hash, $hashes, $sectionName = null)
    {
        $this->dataConfigCollectorMock->expects($this->once())
            ->method('getConfig')
            ->with($sectionName)
            ->willReturn($fullConfig);
        $this->configHashGeneratorMock->expects($this->once())
            ->method('generate')
            ->with($config)
            ->willReturn($hash);
        $this->flagMock->expects($this->once())
            ->method('setFlagData')
            ->willReturn($hashes);
        $this->flagFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->with(['data' => ['flag_code' => Hash::CONFIG_KEY]])
            ->willReturn($this->flagMock);
        $this->flagResourceMock->expects($this->exactly(2))
            ->method('load')
            ->with($this->flagMock, Hash::CONFIG_KEY, 'flag_code');
    }
}
