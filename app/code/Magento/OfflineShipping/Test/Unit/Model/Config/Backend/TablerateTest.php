<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflineShipping\Test\Unit\Model\Config\Backend;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OfflineShipping\Model\Config\Backend\Tablerate;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\TablerateFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TablerateTest extends TestCase
{
    /**
     * @var Tablerate
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $tableateFactoryMock;

    protected function setUp(): void
    {
        $this->tableateFactoryMock =
            $this->getMockBuilder(TablerateFactory::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['create'])
                ->getMock();

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            Tablerate::class,
            ['tablerateFactory' => $this->tableateFactoryMock]
        );
    }

    public function testAfterSave()
    {
        $tablerateMock = $this->getMockBuilder(\Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['uploadAndImport'])
            ->getMock();

        $this->tableateFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($tablerateMock);

        $tablerateMock->expects($this->once())
            ->method('uploadAndImport')
            ->with($this->model);

        $this->model->afterSave();
    }
}
