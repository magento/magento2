<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CheckoutAgreements\Test\Unit\Model;

use Magento\CheckoutAgreements\Api\Data\AgreementInterface;
use Magento\CheckoutAgreements\Model\CheckoutAgreementsList;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CheckoutAgreementsListTest extends TestCase
{
    /**
     * @var CheckoutAgreementsList
     */
    private $model;

    /**
     * @var MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var MockObject
     */
    private $attributesJoinProcessorMock;

    /**
     * @var MockObject
     */
    private $collectionProcessorMock;

    protected function setUp(): void
    {
        $this->collectionFactoryMock = $this->createMock(
            CollectionFactory::class
        );
        $this->attributesJoinProcessorMock = $this->createMock(
            JoinProcessorInterface::class
        );
        $this->collectionProcessorMock = $this->createMock(
            CollectionProcessorInterface::class
        );
        $this->model = new CheckoutAgreementsList(
            $this->collectionFactoryMock,
            $this->attributesJoinProcessorMock,
            $this->collectionProcessorMock
        );
    }

    public function testGetList()
    {
        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class);
        $collectionMock = $this->createMock(
            Collection::class
        );
        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($collectionMock);
        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);
        $this->attributesJoinProcessorMock->expects($this->once())->method('process')->with($collectionMock);
        $agreementMock = $this->getMockForAbstractClass(AgreementInterface::class);
        $collectionMock->expects($this->once())->method('getItems')->willReturn([$agreementMock]);
        $this->assertEquals([$agreementMock], $this->model->getList($searchCriteriaMock));
    }
}
