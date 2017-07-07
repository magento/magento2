<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Language;

use Magento\Framework\App\Language\Dictionary;
use Magento\Framework\Filesystem\DriverPool;

class DictionaryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Language\Dictionary
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $readFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $componentRegistrar;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configFactory;

    protected function setUp()
    {
        $this->readFactory = $this->getMock(
            \Magento\Framework\Filesystem\Directory\ReadFactory::class,
            [],
            [],
            '',
            false
        );
        $this->componentRegistrar = $this->getMock(
            \Magento\Framework\Component\ComponentRegistrar::class,
            [],
            [],
            '',
            false
        );
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
        for ($i = 0; $i < count($data); $i++) {
            $file->expects($this->at($i))->method('readCsv')->will($this->returnValue($data[$i]));
        }
        $file->expects($this->at($i))->method('readCsv')->will($this->returnValue(false));

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

        $languageConfig = $this->getMock(\Magento\Framework\App\Language\Config::class, [], [], '', false);
        $languageConfig->expects($this->any())->method('getCode')->will($this->returnValue('en_US'));
        $languageConfig->expects($this->any())->method('getVendor')->will($this->returnValue('foo'));
        $languageConfig->expects($this->any())->method('getPackage')->will($this->returnValue('en_us'));
        $languageConfig->expects($this->any())->method('getSortOrder')->will($this->returnValue(0));
        $languageConfig->expects($this->any())->method('getUses')->will($this->returnValue([]));

        $this->configFactory->expects($this->any())->method('create')->willReturn($languageConfig);

        $result = $this->model->getDictionary("en_US");
        $this->assertSame($expected, $result);
    }
}
