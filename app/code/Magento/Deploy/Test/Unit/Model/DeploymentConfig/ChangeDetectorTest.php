<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Model\DeploymentConfig;

use Magento\Deploy\Model\DeploymentConfig\ChangeDetector;
use Magento\Deploy\Model\DeploymentConfig\DataCollector;
use Magento\Deploy\Model\DeploymentConfig\Hash;
use Magento\Deploy\Model\DeploymentConfig\Hash\Generator as HashGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChangeDetectorTest extends TestCase
{
    /**
     * @var Hash|MockObject
     */
    private $configHashMock;

    /**
     * @var HashGenerator|MockObject
     */
    private $hashGeneratorMock;

    /**
     * @var DataCollector|MockObject
     */
    private $dataConfigCollectorMock;

    /**
     * @var ChangeDetector
     */
    private $changeDetector;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->configHashMock = $this->getMockBuilder(Hash::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->hashGeneratorMock = $this->getMockBuilder(HashGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataConfigCollectorMock = $this->getMockBuilder(DataCollector::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->changeDetector = new ChangeDetector(
            $this->configHashMock,
            $this->hashGeneratorMock,
            $this->dataConfigCollectorMock
        );
    }

    /**
     * @param string $sectionName
     * @param array $fullConfigData
     * @param string|null $configData
     * @param string $generatedHash
     * @param string $savedHash
     * @param bool $expectedResult
     * @return void
     * @dataProvider hasChangesDataProvider
     */
    public function testHasChanges(
        $sectionName,
        $fullConfigData,
        $configData,
        $generatedHash,
        $savedHash,
        $expectedResult
    ) {
        $this->dataConfigCollectorMock->expects($this->once())
            ->method('getConfig')
            ->with($sectionName)
            ->willReturn($fullConfigData);
        $this->hashGeneratorMock->expects($this->any())
            ->method('generate')
            ->with($configData)
            ->willReturn($generatedHash);
        $this->configHashMock->expects($this->any())
            ->method('get')
            ->willReturn($savedHash);

        $this->assertSame($expectedResult, $this->changeDetector->hasChanges($sectionName));
    }

    /**
     * @return array
     */
    public function hasChangesDataProvider()
    {
        return [
            [
                'sectionName' => null,
                'fullConfigData' => ['section' => 'some data'],
                'configData' => 'some data',
                'generatedHash' => '123',
                'savedHash' => ['section' => '123'],
                'expectedResult' => false
            ],
            [
                'sectionName' => 'section',
                'fullConfigData' => ['section' => 'some data'],
                'configData' => 'some data',
                'generatedHash' => '321',
                'savedHash' => ['section' => '123'],
                'expectedResult' => true
            ],
            [
                'sectionName' => null,
                'fullConfigData' => ['section' => 'some data'],
                'configData' => 'some data',
                'generatedHash' => '321',
                'savedHash' => [],
                'expectedResult' => true
            ],
            [
                'sectionName' => 'section',
                'fullConfigData' => [],
                'configData' => null,
                'generatedHash' => '321',
                'savedHash' => ['section' => '123'],
                'expectedResult' => false
            ],
            [
                'sectionName' => null,
                'fullConfigData' => [],
                'configData' => null,
                'generatedHash' => '321',
                'savedHash' => [],
                'expectedResult' => false
            ],
        ];
    }
}
