<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Model;

use Magento\Config\Model\PreparedValueFactory;
use Magento\Deploy\Model\ConfigWriter;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Stdlib\ArrayManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigWriterTest extends TestCase
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
     * @var PreparedValueFactory|MockObject
     */
    private $preparedValueFactoryMock;

    /**
     * @var ValueInterface|MockObject
     */
    private $valueInterfaceMock;

    /**
     * @var Value|MockObject
     */
    private $valueMock;

    /**
     * @var ConfigWriter
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->arrayManagerMock = $this->getMockBuilder(ArrayManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->writerMock = $this->getMockBuilder(Writer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->preparedValueFactoryMock = $this->getMockBuilder(PreparedValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->valueInterfaceMock = $this->getMockBuilder(ValueInterface::class)
            ->getMockForAbstractClass();
        $this->valueMock = $this->getMockBuilder(Value::class)
            ->disableOriginalConstructor()
            ->setMethods(['validateBeforeSave', 'beforeSave', 'getValue', 'afterSave'])
            ->getMock();

        $this->model = new ConfigWriter(
            $this->writerMock,
            $this->arrayManagerMock,
            $this->preparedValueFactoryMock
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

        $this->preparedValueFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->withConsecutive(
                ['some1/config1/path1', 'someValue1', 'scope', 'scope_code'],
                ['some2/config2/path2', 'someValue2', 'scope', 'scope_code'],
                ['some3/config3/path3', 'someValue3', 'scope', 'scope_code']
            )
            ->willReturnOnConsecutiveCalls(
                $this->valueInterfaceMock,
                $this->valueMock,
                $this->valueMock
            );

        $this->valueMock->expects($this->exactly(2))
            ->method('validateBeforeSave');
        $this->valueMock->expects($this->exactly(2))
            ->method('beforeSave');
        $this->valueMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnOnConsecutiveCalls('someValue2', 'someValue3');
        $this->valueMock->expects($this->exactly(2))
            ->method('afterSave');

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
            ->with([ConfigFilePool::APP_ENV => $config]);

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

        $this->preparedValueFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->withConsecutive(
                ['some1/config1/path1', 'someValue1', 'default'],
                ['some2/config2/path2', 'someValue2', 'default'],
                ['some3/config3/path3', 'someValue3', 'default']
            )
            ->willReturnOnConsecutiveCalls(
                $this->valueInterfaceMock,
                $this->valueMock,
                $this->valueMock
            );

        $this->valueMock->expects($this->exactly(2))
            ->method('validateBeforeSave');
        $this->valueMock->expects($this->exactly(2))
            ->method('beforeSave');
        $this->valueMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnOnConsecutiveCalls('someValue2', 'someValue3');
        $this->valueMock->expects($this->exactly(2))
            ->method('afterSave');

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
            ->with([ConfigFilePool::APP_ENV => $config]);

        $this->model->save($values);
    }

    /**
     * Save null (empty input) through CLI and assert it does not create backend model for validation
     * @return void
     */
    public function testSavingNullValues()
    {
        $values = [
            'some1/config1/path1' => null,
        ];

        $this->preparedValueFactoryMock->expects($this->never())->method('create');

        $this->writerMock->expects($this->once())
            ->method('saveConfig')
            ->with([ConfigFilePool::APP_ENV => []]);

        $this->model->save($values);
    }
}
