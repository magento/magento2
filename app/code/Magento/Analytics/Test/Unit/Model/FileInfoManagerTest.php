<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\FileInfo;
use Magento\Analytics\Model\FileInfoFactory;
use Magento\Analytics\Model\FileInfoManager;
use Magento\Framework\FlagManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileInfoManagerTest extends TestCase
{
    /**
     * @var FlagManager|MockObject
     */
    private $flagManagerMock;

    /**
     * @var FileInfoFactory|MockObject
     */
    private $fileInfoFactoryMock;

    /**
     * @var FileInfo|MockObject
     */
    private $fileInfoMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var FileInfoManager
     */
    private $fileInfoManager;

    /**
     * @var string
     */
    private $flagCode = 'analytics_file_info';

    /**
     * @var array
     */
    private $encodedParameters = [
        'initializationVector'
    ];

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->flagManagerMock = $this->createMock(FlagManager::class);

        $this->fileInfoFactoryMock = $this->getMockBuilder(FileInfoFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileInfoMock = $this->createMock(FileInfo::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->fileInfoManager = $this->objectManagerHelper->getObject(
            FileInfoManager::class,
            [
                'flagManager' => $this->flagManagerMock,
                'fileInfoFactory' => $this->fileInfoFactoryMock,
                'flagCode' => $this->flagCode,
                'encodedParameters' => $this->encodedParameters,
            ]
        );
    }

    /**
     * @return void
     */
    public function testSave()
    {
        $path = 'path/to/file';
        $initializationVector = openssl_random_pseudo_bytes(16);
        $parameters = [
            'path' => $path,
            'initializationVector' => $initializationVector,
        ];

        $this->fileInfoMock
            ->expects($this->once())
            ->method('getPath')
            ->with()
            ->willReturn($path);
        $this->fileInfoMock
            ->expects($this->once())
            ->method('getInitializationVector')
            ->with()
            ->willReturn($initializationVector);

        foreach ($this->encodedParameters as $encodedParameter) {
            $parameters[$encodedParameter] = base64_encode($parameters[$encodedParameter]);
        }
        $this->flagManagerMock
            ->expects($this->once())
            ->method('saveFlag')
            ->with($this->flagCode, $parameters);

        $this->assertTrue($this->fileInfoManager->save($this->fileInfoMock));
    }

    /**
     * @param string|null $path
     * @param string|null $initializationVector
     * @dataProvider saveWithLocalizedExceptionDataProvider
     */
    public function testSaveWithLocalizedException($path, $initializationVector)
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->fileInfoMock
            ->expects($this->once())
            ->method('getPath')
            ->with()
            ->willReturn($path);
        $this->fileInfoMock
            ->expects($this->once())
            ->method('getInitializationVector')
            ->with()
            ->willReturn($initializationVector);

        $this->fileInfoManager->save($this->fileInfoMock);
    }

    /**
     * @return array
     */
    public function saveWithLocalizedExceptionDataProvider()
    {
        return [
            'Empty FileInfo' => [null, null],
            'FileInfo without IV' => ['path/to/file', null],
        ];
    }

    /**
     * @dataProvider loadDataProvider
     * @param array|null $parameters
     */
    public function testLoad($parameters)
    {
        $this->flagManagerMock
            ->expects($this->once())
            ->method('getFlagData')
            ->with($this->flagCode)
            ->willReturn($parameters);

        $processedParameters = $parameters ?: [];
        $encodedParameters = array_intersect($this->encodedParameters, array_keys($processedParameters));
        foreach ($encodedParameters as $encodedParameter) {
            $processedParameters[$encodedParameter] = base64_decode($processedParameters[$encodedParameter]);
        }

        $this->fileInfoFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($processedParameters)
            ->willReturn($this->fileInfoMock);

        $this->assertSame($this->fileInfoMock, $this->fileInfoManager->load());
    }

    /**
     * @return array
     */
    public function loadDataProvider()
    {
        return [
            'Empty flag data' => [null],
            'Correct flag data' => [[
                'path' => 'path/to/file',
                'initializationVector' => 'xUJjl54MVke+FvMFSBpRSA==',
            ]],
        ];
    }
}
