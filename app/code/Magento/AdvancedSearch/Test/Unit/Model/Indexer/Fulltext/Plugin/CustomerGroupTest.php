<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedSearch\Test\Unit\Model\Indexer\Fulltext\Plugin;

use Magento\AdvancedSearch\Model\Indexer\Fulltext\Plugin\CustomerGroup;
use Magento\Framework\Search\EngineResolverInterface;

class CustomerGroupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Indexer\IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Customer\Model\ResourceModel\Group
     */
    protected $subjectMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\AdvancedSearch\Model\Client\ClientOptionsInterface
     */
    protected $customerOptionsMock;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var EngineResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $engineResolverMock;

    /**
     * @var CustomerGroup
     */
    protected $model;

    protected function setUp(): void
    {
        $this->subjectMock = $this->createMock(\Magento\Customer\Model\ResourceModel\Group::class);
        $this->customerOptionsMock = $this->createMock(
            \Magento\AdvancedSearch\Model\Client\ClientOptionsInterface::class
        );
        $this->indexerMock = $this->getMockForAbstractClass(
            \Magento\Framework\Indexer\IndexerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getId', 'getState', '__wakeup']
        );
        $this->indexerRegistryMock = $this->createPartialMock(
            \Magento\Framework\Indexer\IndexerRegistry::class,
            ['get']
        );
        $this->engineResolverMock = $this->createPartialMock(
            \Magento\Search\Model\EngineResolver::class,
            ['getCurrentSearchEngine']
        );
        $this->model = new CustomerGroup(
            $this->indexerRegistryMock,
            $this->customerOptionsMock,
            $this->engineResolverMock
        );
    }

    /**
     * @param string $searchEngine
     * @param bool $isObjectNew
     * @param bool $isTaxClassIdChanged
     * @param int $invalidateCounter
     * @return void
     * @dataProvider aroundSaveDataProvider
     */
    public function testAroundSave($searchEngine, $isObjectNew, $isTaxClassIdChanged, $invalidateCounter)
    {
        $this->engineResolverMock->expects($this->once())
            ->method('getCurrentSearchEngine')
            ->willReturn($searchEngine);

        $groupMock = $this->createPartialMock(
            \Magento\Customer\Model\Group::class,
            ['dataHasChangedFor', 'isObjectNew', '__wakeup']
        );
        $groupMock->expects($this->any())->method('isObjectNew')->willReturn($isObjectNew);
        $groupMock->expects($this->any())
            ->method('dataHasChangedFor')
            ->with('tax_class_id')
            ->willReturn($isTaxClassIdChanged);

        $closureMock = function (\Magento\Customer\Model\Group $object) use ($groupMock) {
            $this->assertEquals($object, $groupMock);
            return $this->subjectMock;
        };

        $this->indexerMock->expects($this->exactly($invalidateCounter))->method('invalidate');
        $this->indexerRegistryMock->expects($this->exactly($invalidateCounter))
            ->method('get')
            ->with(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID)
            ->willReturn($this->indexerMock);

        $this->assertEquals(
            $this->subjectMock,
            $this->model->aroundSave($this->subjectMock, $closureMock, $groupMock)
        );
    }

    /**
     * @return array
     */
    public function aroundSaveDataProvider()
    {
        return [
            ['mysql', false, false, 0],
            ['mysql', false, true, 0],
            ['mysql', true, false, 0],
            ['mysql', true, true, 0],
            ['custom', false, false, 0],
            ['custom', false, true, 1],
            ['custom', true, false, 1],
            ['custom', true, true, 1],
        ];
    }
}
