<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model;

use Magento\Deploy\Model\ConfigWriter;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Stdlib\ArrayManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ConfigWriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Writer|MockObject
     */
    private $writerMock;

    /**
     * @var ArrayManager|MockObject
     */
    private $arrayManagerMock;

    /**
     * @var ConfigWriter
     */
    private $model;

    public function setUp()
    {
        $this->arrayManagerMock = $this->getMockBuilder(ArrayManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->writerMock = $this->getMockBuilder(Writer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new ConfigWriter(
            $this->writerMock,
            $this->arrayManagerMock
        );
    }

    public function testSave()
    {
        $values = [
            'some1/config1/path1' => 'someValue1',
            'some2/config2/path2' => 'someValue2',
            'some3/config3/path3' => 'someValue3'
        ];
        $config = ['system' => []];

        $this->arrayManagerMock->expects($this->exactly(3))
            ->method('set')
            ->withConsecutive(
                ['system/scope/scope_code/some1/config1/path1', $this->anything(), 'someValue1'],
                ['system/scope/scope_code/some2/config2/path2', $this->anything(), 'someValue2'],
                ['system/scope/scope_code/some3/config3/path3', $this->anything(), 'someValue3']
            )
            ->willReturn($config);
        $this->writerMock->expects($this->once())
            ->method('saveConfig')
            ->with([ConfigFilePool::APP_CONFIG => $config]);

        $this->model->save($values, 'scope', 'scope_code');
    }

    public function testSaveDefaultScope()
    {
        $values = [
            'some1/config1/path1' => 'someValue1',
            'some2/config2/path2' => 'someValue2',
            'some3/config3/path3' => 'someValue3'
        ];
        $config = ['system' => []];

        $this->arrayManagerMock->expects($this->exactly(3))
            ->method('set')
            ->withConsecutive(
                ['system/default/some1/config1/path1', $this->anything(), 'someValue1'],
                ['system/default/some2/config2/path2', $this->anything(), 'someValue2'],
                ['system/default/some3/config3/path3', $this->anything(), 'someValue3']
            )
            ->willReturn($config);
        $this->writerMock->expects($this->once())
            ->method('saveConfig')
            ->with([ConfigFilePool::APP_CONFIG => $config]);

        $this->model->save($values);
    }
}
