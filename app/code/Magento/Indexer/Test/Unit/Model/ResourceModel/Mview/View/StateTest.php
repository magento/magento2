<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model\ResourceModel\Mview\View;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Mview\View\StateInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Indexer\Model\ResourceModel\Mview\View\State;
use PHPUnit\Framework\TestCase;

class StateTest extends TestCase
{
    /**
     * @var State
     */
    protected $model;

    public function testConstruct()
    {
        $resourceMock = $this->createMock(ResourceConnection::class);
        $objectManager = new ObjectManager($this);
        $arguments = $objectManager->getConstructArguments(
            State::class,
            ['resource' => $resourceMock]
        );
        $this->model = $objectManager->getObject(
            State::class,
            $arguments
        );
        $this->assertEquals(
            [['field' => ['view_id'], 'title' => __('State for the same view')]],
            $this->model->getUniqueFields()
        );
    }

    /**
     * @given A checkpoint targets a specific view and version.
     * @when The resource builds the cursor update.
     * @then The update requires that view to be working with an older version.
     */
    public function testUpdateVersionIdIfWorkingUpdatesOnlyTheForwardCursor(): void
    {
        $viewId = 'catalog_product_attribute';
        $versionId = 17;
        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->once())
            ->method('update')
            ->with(
                'mview_state',
                ['version_id' => $versionId],
                [
                    'view_id = ?' => $viewId,
                    'status = ?' => StateInterface::STATUS_WORKING,
                    'version_id < ?' => $versionId,
                ]
            )
            ->willReturn(1);

        $resource = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConnection', 'getMainTable'])
            ->getMock();
        $resource->method('getConnection')->willReturn($connection);
        $resource->method('getMainTable')->willReturn('mview_state');

        $this->assertSame(1, $resource->updateVersionIdIfWorking($viewId, $versionId));
    }
}
