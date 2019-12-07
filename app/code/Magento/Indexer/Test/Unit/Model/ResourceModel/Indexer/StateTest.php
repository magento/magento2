<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Model\ResourceModel\Indexer;

class StateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Indexer\Model\ResourceModel\Indexer\State
     */
    protected $model;

    public function testConstruct()
    {
        $resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $arguments = $objectManager->getConstructArguments(
            \Magento\Indexer\Model\ResourceModel\Indexer\State::class,
            ['resource' => $resourceMock]
        );
        $this->model = $objectManager->getObject(
            \Magento\Indexer\Model\ResourceModel\Indexer\State::class,
            $arguments
        );
        $this->assertEquals(
            [['field' => ['indexer_id'], 'title' => __('State for the same indexer')]],
            $this->model->getUniqueFields()
        );
    }
}
