<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model\DeploymentConfig;

use Magento\Deploy\Model\DeploymentConfig\Hash\Generator as HashGenerator;
use Magento\Deploy\Model\DeploymentConfig\Hash;
use Magento\Deploy\Model\DeploymentConfig\DataCollector;
use Magento\Deploy\Model\DeploymentConfig\Validator;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Hash|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configHashMock;

    /**
     * @var HashGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $hashGeneratorMock;

    /**
     * @var DataCollector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataConfigCollectorMock;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @return void
     */
    protected function setUp()
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

        $this->validator = new Validator(
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
     * @dataProvider isValidDataProvider
     */
    public function testIsValid(
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

        $this->assertSame($expectedResult, $this->validator->isValid($sectionName));
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            [
                'sectionName' => null,
                'fullConfigData' => ['section' => 'some data'],
                'configData' => 'some data',
                'generatedHash' => '123',
                'savedHash' => ['section' => '123'],
                'expectedResult' => true
            ],
            [
                'sectionName' => 'section',
                'fullConfigData' => ['section' => 'some data'],
                'configData' => 'some data',
                'generatedHash' => '321',
                'savedHash' => ['section' => '123'],
                'expectedResult' => false
            ],
            [
                'sectionName' => null,
                'fullConfigData' => ['section' => 'some data'],
                'configData' => 'some data',
                'generatedHash' => '321',
                'savedHash' => [],
                'expectedResult' => false
            ],
            [
                'sectionName' => 'section',
                'fullConfigData' => [],
                'configData' => null,
                'generatedHash' => '321',
                'savedHash' => ['section' => '123'],
                'expectedResult' => true],
            [
                'sectionName' => null,
                'fullConfigData' => [],
                'configData' => null,
                'generatedHash' => '321',
                'savedHash' => [],
                'expectedResult' => true
            ],
        ];
    }
}
