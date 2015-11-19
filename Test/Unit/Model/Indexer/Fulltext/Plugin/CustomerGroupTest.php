<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @var CustomerGroup
     */
    protected $model;

    protected function setUp()
    {
        $this->subjectMock = $this->getMock('Magento\Customer\Model\ResourceModel\Group', [], [], '', false);
        $this->customerOptionsMock = $this->getMock(
            'Magento\AdvancedSearch\Model\Client\ClientOptionsInterface',
            [],
            [],
            '',
            false
        );
        $this->indexerMock = $this->getMockForAbstractClass(
            'Magento\Framework\Indexer\IndexerInterface',
            [],
            '',
            false,
            false,
            true,
            ['getId', 'getState', '__wakeup']
        );
        $this->indexerRegistryMock = $this->getMock(
            'Magento\Framework\Indexer\IndexerRegistry',
            ['get'],
            [],
            '',
            false
        );
        $this->model = new CustomerGroup(
            $this->indexerRegistryMock,
            $this->customerOptionsMock
        );
    }

    /**
     * @param bool $isThirdPartyEngineAvailable
     * @param bool $isObjectNew
     * @param bool $isTaxClassIdChanged
     * @param int $invalidateCounter
     * @return void
     * @dataProvider aroundSaveDataProvider
     */
    public function testAroundSave($isThirdPartyEngineAvailable, $isObjectNew, $isTaxClassIdChanged, $invalidateCounter)
    {
        $this->customerOptionsMock->expects($this->once())
            ->method('isThirdPartyEngineAvailable')
            ->will($this->returnValue($isThirdPartyEngineAvailable));

        $groupMock = $this->getMock(
            'Magento\Customer\Model\Group',
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
            [false, false, false, 0],
            [false, false, true, 0],
            [false, true, false, 0],
            [false, true, true, 0],
            [true, false, false, 0],
            [true, false, true, 1],
            [true, true, false, 1],
            [true, true, true, 1],
        ];
    }
}
