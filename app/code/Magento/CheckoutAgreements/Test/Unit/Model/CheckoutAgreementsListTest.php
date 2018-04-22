<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Test\Unit\Model;

class CheckoutAgreementsListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CheckoutAgreements\Model\CheckoutAgreementsList
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $attributesJoinProcessorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessorMock;

    protected function setUp()
    {
        $this->collectionFactoryMock = $this->createMock(
            \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory::class
        );
        $this->attributesJoinProcessorMock = $this->createMock(
            \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface::class
        );
        $this->collectionProcessorMock = $this->createMock(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
        );
        $this->model = new \Magento\CheckoutAgreements\Model\CheckoutAgreementsList(
            $this->collectionFactoryMock,
            $this->attributesJoinProcessorMock,
            $this->collectionProcessorMock
        );
    }

    public function testGetList()
    {
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $collectionMock = $this->createMock(
            \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection::class
        );
        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($collectionMock);
        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);
        $this->attributesJoinProcessorMock->expects($this->once())->method('process')->with($collectionMock);
        $agreementMock = $this->createMock(\Magento\CheckoutAgreements\Api\Data\AgreementInterface::class);
        $collectionMock->expects($this->once())->method('getItems')->willReturn([$agreementMock]);
        $this->assertEquals([$agreementMock], $this->model->getList($searchCriteriaMock));
    }
}
