<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedSearch\Test\Unit\Model\Indexer\Fulltext\Plugin;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Customer\Model\ResourceModel\Group;
use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Search\Model\EngineResolver;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\AdvancedSearch\Model\Indexer\Fulltext\Plugin\CustomerGroup;
use Magento\Framework\Search\EngineResolverInterface;

class CustomerGroupTest extends TestCase
{
    /**
     * @var MockObject|IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var MockObject|Group
     */
    protected $subjectMock;

    /**
     * @var MockObject|ClientOptionsInterface
     */
    protected $customerOptionsMock;

    /**
     * @var IndexerRegistry|MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var EngineResolverInterface|MockObject
     */
    protected $engineResolverMock;

    /**
     * @var CustomerGroup
     */
    protected $model;

    protected function setUp(): void
    {
        $this->subjectMock = $this->createMock(Group::class);
        $this->customerOptionsMock = $this->createMock(
            ClientOptionsInterface::class
        );
        $this->indexerMock = $this->getMockForAbstractClass(
            IndexerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getId', 'getState', '__wakeup']
        );
        $this->indexerRegistryMock = $this->createPartialMock(
            IndexerRegistry::class,
            ['get']
        );
        $this->engineResolverMock = $this->createPartialMock(
            EngineResolver::class,
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
            ->will($this->returnValue($searchEngine));

        $groupMock = $this->createPartialMock(
            \Magento\Customer\Model\Group::class,
            ['dataHasChangedFor', 'isObjectNew', '__wakeup']
        );
        $groupMock->method('isObjectNew')->will($this->returnValue($isObjectNew));
        $groupMock
            ->method('dataHasChangedFor')
            ->with('tax_class_id')
            ->will($this->returnValue($isTaxClassIdChanged));

        $closureMock = function (\Magento\Customer\Model\Group $object) use ($groupMock) {
            $this->assertEquals($object, $groupMock);
            return $this->subjectMock;
        };

        $this->indexerMock->expects($this->exactly($invalidateCounter))->method('invalidate');
        $this->indexerRegistryMock->expects($this->exactly($invalidateCounter))
            ->method('get')
            ->with(Fulltext::INDEXER_ID)
            ->will($this->returnValue($this->indexerMock));

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
