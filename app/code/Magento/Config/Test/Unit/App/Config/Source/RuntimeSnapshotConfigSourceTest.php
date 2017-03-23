<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\App\Config\Source;

use Magento\Config\App\Config\Source\RuntimeSnapshotConfigSource;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Flag;
use Magento\Framework\FlagFactory;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * @inheritdoc
 */
class RuntimeSnapshotConfigSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RuntimeSnapshotConfigSource
     */
    private $model;

    /**
     * @var FlagFactory
     */
    private $flagFactoryMock;

    /**
     * @var Flag|Mock
     */
    private $flagMock;

    /**
     * @var Flag\FlagResource|Mock
     */
    private $flagResourceMock;

    /**
     * @var DataObjectFactory|Mock
     */
    private $dataObjectFactoryMock;

    /**
     * @var DataObject|Mock
     */
    private $dataObjectMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->flagFactoryMock = $this->getMockBuilder(FlagFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->flagMock = $this->getMockBuilder(Flag::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->flagResourceMock = $this->getMockBuilder(Flag\FlagResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectFactoryMock = $this->getMockBuilder(DataObjectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->flagFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->flagMock);
        $this->dataObjectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->dataObjectMock);

        $this->model = new RuntimeSnapshotConfigSource(
            $this->flagFactoryMock,
            $this->dataObjectFactoryMock
        );
    }

    public function testGet()
    {
        $this->flagMock->expects($this->exactly(2))
            ->method('getResource')
            ->willReturn($this->flagResourceMock);
        $this->flagResourceMock->expects($this->exactly(2))
            ->method('load')
            ->with($this->flagMock, 'system_config_snapshot', 'flag_code');
        $this->dataObjectMock->expects($this->exactly(2))
            ->method('getData')
            ->willReturnOnConsecutiveCalls(
                ['some' => 'data'],
                'data'
            );

        $this->assertSame(['some' => 'data'], $this->model->get());
        $this->assertSame('data', $this->model->get('some/path'));
    }
}
