<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model\DeploymentConfig;

use Magento\Deploy\Model\DeploymentConfig\Hash\Generator as HashGenerator;
use Magento\Deploy\Model\DeploymentConfig\Hash;
use Magento\Deploy\Model\DeploymentConfig\DataCollector;
use Magento\Deploy\Model\DeploymentConfig\ChangeDetector;

class ChangeDetectorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Hash|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configHashMock;

    /**
     * @var HashGenerator|\PHPUnit\Framework\MockObject\MockObject
     */
    private $hashGeneratorMock;

    /**
     * @var DataCollector|\PHPUnit\Framework\MockObject\MockObject
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
