<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedSearch\Test\Unit\Model\Indexer\Fulltext\Plugin;

use Magento\AdvancedSearch\Model\Indexer\Fulltext\Plugin\CustomerGroup;

class CustomerGroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Indexer\IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Model\ResourceModel\Group
     */
    protected $subjectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\AdvancedSearch\Model\Client\ClientOptionsInterface
     */
    protected $customerOptionsMock;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var \Magento\Search\Model\EngineResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $engineResolverMock;

    /**
     * @var CustomerGroup
     */
    protected $model;

    protected function setUp()
    {
        $this->subjectMock = $this->getMock(\Magento\Customer\Model\ResourceModel\Group::class, [], [], '', false);
        $this->customerOptionsMock = $this->getMock(
            \Magento\AdvancedSearch\Model\Client\ClientOptionsInterface::class,
            [],
            [],
            '',
            false
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
        $this->indexerRegistryMock = $this->getMock(
            \Magento\Framework\Indexer\IndexerRegistry::class,
            ['get'],
            [],
            '',
            false
        );
        $this->engineResolverMock = $this->getMock(
            \Magento\Search\Model\EngineResolver::class,
            ['getCurrentSearchEngine'],
            [],
            '',
            false
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

        $groupMock = $this->getMock(
            \Magento\Customer\Model\Group::class,
            ['dataHasChangedFor', 'isObjectNew', '__wakeup'],
            [],
            '',
            false
        );
        $groupMock->expects($this->any())->method('isObjectNew')->will($this->returnValue($isObjectNew));
        $groupMock->expects($this->any())
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
            ->with(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID)
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
