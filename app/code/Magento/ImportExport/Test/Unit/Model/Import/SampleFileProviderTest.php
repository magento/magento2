<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model\Import;

use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ImportExport\Model\Import\SampleFileProvider;

/**
 * Test class for Magento\ImportExport\Model\Import\SampleFileProvider.
 */
class SampleFileProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readerMock;

    /**
     * @var ReadFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readerFactoryMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var SampleFileProvider
     */
    private $model;

    /**
     * @var string
     */
    private $entityName = 'test_sample';

    /**
     * @var string
     */
    private $moduleName = 'Test_Sample';

    /**
     * @var string
     */
    private $filePath = 'Files/Sample/test_sample.csv';

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->readerMock = $this->getMockBuilder(ReadInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->readerFactoryMock = $this->createMock(ReadFactory::class);
        $this->readerFactoryMock->expects($this->any())->method('create')->willReturn($this->readerMock);
        $this->readerMock->expects($this->any())->method('getRelativePath')->willReturnArgument(0);
        $this->model = $this->objectManager->getObject(
            SampleFileProvider::class,
            [
                'readFactory' => $this->readerFactoryMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetSize()
    {
        $fileSize = 10;

        $this->objectManager->setBackwardCompatibleProperty(
            $this->model,
            'samples',
            [$this->entityName => $this->moduleName]
        );
        $this->readerMock->expects($this->atLeastOnce())->method('isFile')->willReturn(true);
        $this->readerMock->expects($this->once())->method('stat')
            ->with($this->filePath)
            ->willReturn(['size' => $fileSize]);

        $actualSize = $this->model->getSize($this->entityName);
        $this->assertEquals($fileSize, $actualSize);
    }

    /**
     * @return void
     */
    public function testGetFileContents()
    {
        $fileContent = 'test';

        $this->objectManager->setBackwardCompatibleProperty(
            $this->model,
            'samples',
            [$this->entityName => $this->moduleName]
        );
        $this->readerMock->expects($this->atLeastOnce())->method('isFile')->willReturn(true);
        $this->readerMock->expects($this->once())->method('readFile')
            ->with($this->filePath)
            ->willReturn($fileContent);

        $actualContent = $this->model->getFileContents($this->entityName);
        $this->assertEquals($fileContent, $actualContent);
    }

    /**
     * @dataProvider methodDataProvider
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @param string $methodName
     * @return void
     */
    public function testMethodCallMissingSample(string $methodName)
    {
        $this->model->{$methodName}('missingType');
    }

    /**
     * @dataProvider methodDataProvider
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage There is no file: Files/Sample/test_sample.csv
     * @param string $methodName
     * @return void
     */
    public function testMethodCallMissingFile(string $methodName)
    {
        $this->objectManager->setBackwardCompatibleProperty(
            $this->model,
            'samples',
            [$this->entityName => $this->moduleName]
        );
        $this->readerMock->expects($this->atLeastOnce())->method('isFile')->willReturn(false);

        $this->model->{$methodName}($this->entityName);
    }

    /**
     * @return array
     */
    public function methodDataProvider(): array
    {
        return [
            ['getSize'],
            ['getFileContents'],
        ];
    }
}
