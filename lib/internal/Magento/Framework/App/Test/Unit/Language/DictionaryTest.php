<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Language;

use Magento\Framework\App\Language\Dictionary;
use Magento\Framework\Filesystem\DriverPool;

class DictionaryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Language\Dictionary
     */
    private $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $readFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $componentRegistrar;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $configFactory;

    protected function setUp(): void
    {
        $this->readFactory = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadFactory::class);
        $this->componentRegistrar = $this->createMock(\Magento\Framework\Component\ComponentRegistrar::class);
        $this->configFactory = $this->getMockBuilder(\Magento\Framework\App\Language\ConfigFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new Dictionary($this->readFactory, $this->componentRegistrar, $this->configFactory);
    }

    public function testDictionaryGetter()
    {
        $csvFileName = 'abc.csv';
        $data = [['one', '1'], ['two', '2']];
        $expected = [];
        foreach ($data as $item) {
            $expected[$item[0]] = $item[1];
        }

        $file = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\File\ReadInterface::class);
        for ($i = 0, $count = count($data); $i < $count; $i++) {
            $file->expects($this->at($i))->method('readCsv')->willReturn($data[$i]);
        }
        $file->expects($this->at($i))->method('readCsv')->willReturn(false);

        $readMock = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $readMock->expects($this->any())->method('readFile')->willReturnMap([
            ['language.xml', $readMock],
            [$csvFileName, $file],
        ]);
        $readMock->expects($this->any())->method('openFile')->willReturn($file);
        $readMock->expects($this->any())->method('isExist')->willReturn(true);
        $readMock->expects($this->any())->method('search')->willReturn([$csvFileName]);

        $this->componentRegistrar->expects($this->once())->method('getPaths')->willReturn(['foo/en_us']);
        $this->componentRegistrar->expects($this->once())->method('getPath')->willReturn('foo/en_us');

        $this->readFactory->expects($this->any())->method("create")->willReturn($readMock);

        $languageConfig = $this->createMock(\Magento\Framework\App\Language\Config::class);
        $languageConfig->expects($this->any())->method('getCode')->willReturn('en_US');
        $languageConfig->expects($this->any())->method('getVendor')->willReturn('foo');
        $languageConfig->expects($this->any())->method('getPackage')->willReturn('en_us');
        $languageConfig->expects($this->any())->method('getSortOrder')->willReturn(0);
        $languageConfig->expects($this->any())->method('getUses')->willReturn([]);

        $this->configFactory->expects($this->any())->method('create')->willReturn($languageConfig);

        $result = $this->model->getDictionary("en_US");
        $this->assertSame($expected, $result);
    }
}
