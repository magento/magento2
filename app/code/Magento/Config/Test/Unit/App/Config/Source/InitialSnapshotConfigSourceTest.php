<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\App\Config\Source;

use Magento\Config\App\Config\Source\InitialSnapshotConfigSource;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\FlagManager;
use PHPUnit\Framework\MockObject\MockObject as Mock;

/**
 * @inheritdoc
 */
class InitialSnapshotConfigSourceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var InitialSnapshotConfigSource
     */
    private $model;

    /**
     * @var FlagManager|Mock
     */
    private $flagManagerMock;

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
    protected function setUp(): void
    {
        $this->flagManagerMock = $this->getMockBuilder(FlagManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectFactoryMock = $this->getMockBuilder(DataObjectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataObjectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->dataObjectMock);

        $this->model = new InitialSnapshotConfigSource(
            $this->flagManagerMock,
            $this->dataObjectFactoryMock
        );
    }

    public function testGet()
    {
        $this->flagManagerMock->expects($this->exactly(2))
            ->method('getFlagData')
            ->with('system_config_snapshot')
            ->willReturnOnConsecutiveCalls(
                ['some' => 'data'],
                'data'
            );
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
