<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Vault\Test\Unit\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResults;
use Magento\Vault\Api\Data\PaymentTokenSearchResultsInterfaceFactory;
use Magento\Vault\Model\PaymentToken;
use Magento\Vault\Model\PaymentTokenFactory;
use Magento\Vault\Model\PaymentTokenRepository;
use Magento\Vault\Model\ResourceModel\PaymentToken as PaymentTokenResourceModel;
use Magento\Vault\Model\ResourceModel\PaymentToken\Collection;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentTokenRepositoryTest extends TestCase
{
    const PUBLIC_HASH = 'hash';

    /**
     * @var PaymentTokenRepository|MockObject resourceModelMock
     */
    protected $repositoryModel;

    /**
     * @var \Magento\Vault\Model\ResourceModel\PaymentToken|MockObject resourceModelMock
     */
    protected $resourceModelMock;

    /**
     * @var PaymentTokenFactory|MockObject paymentTokenFactoryMock
     */
    protected $paymentTokenFactoryMock;

    /**
     * @var \Magento\Vault\Model\PaymentToken|MockObject paymentTokenMock
     */
    protected $paymentTokenMock;

    /**
     * @var FilterBuilder|MockObject filterBuilderMock
     */
    protected $filterBuilderMock;

    /**
     * @var SearchCriteriaBuilder|MockObject searchCriteriaBuilder
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var SearchCriteria|MockObject searchCriteriaMock
     */
    protected $searchCriteriaMock;

    /**
     * @var PaymentTokenSearchResultsInterfaceFactory|MockObject searchResultsFactoryMock
     */
    protected $searchResultsFactoryMock;

    /**
     * @var SearchResults searchResults
     */
    protected $searchResults;

    /**
     * @var CollectionFactory|MockObject collectionFactoryMock
     */
    protected $collectionFactoryMock;

    /**
     * @var Collection|MockObject collection
     */
    protected $collectionMock;

    /**
     * @var MockObject
     */
    private $collectionProcessor;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->resourceModelMock = $this->getMockBuilder(PaymentTokenResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentTokenMock = $this->getMockBuilder(PaymentToken::class)
            ->onlyMethods(['save', 'load'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentTokenMock->setIsActive(true);
        $this->paymentTokenMock->setPublicHash(PaymentTokenRepositoryTest::PUBLIC_HASH);
        $this->paymentTokenFactoryMock = $this->getMockBuilder(PaymentTokenFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterBuilderMock = $this->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchResultsFactoryMock = $this->getMockBuilder(PaymentTokenSearchResultsInterfaceFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchResults = new SearchResults();

        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionProcessor = $this->createMock(
            CollectionProcessorInterface::class
        );
        $this->repositoryModel = $this->getMockBuilder(PaymentTokenRepository::class)
            ->setConstructorArgs([
                'resourceModel' => $this->resourceModelMock,
                'paymentTokenFactory' => $this->paymentTokenFactoryMock,
                'filterBuilder' => $this->filterBuilderMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'searchResultsFactory' => $this->searchResultsFactoryMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'collectionProcessor' => $this->collectionProcessor
            ])
            ->onlyMethods([])
            ->getMock();
    }

    public function testRepositoryGetList()
    {
        $this->collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->paymentTokenMock]);

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->searchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->searchResults);
        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($this->searchCriteriaMock, $this->collectionMock);
        $list = $this->repositoryModel->getList($this->searchCriteriaMock);
        $this->assertSame($this->searchResults, $list);
        $this->assertSame(
            $this->paymentTokenMock,
            $list->getItems()[0]
        );
    }

    public function testRepositoryGetById()
    {
        $this->paymentTokenFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->paymentTokenMock);
        $this->resourceModelMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();

        $this->assertSame($this->paymentTokenMock, $this->repositoryModel->getById(10));
    }

    public function testRepositoryDelete()
    {
        $this->paymentTokenFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->paymentTokenMock);
        $this->resourceModelMock->expects($this->exactly(2))
            ->method('load')
            ->willReturnSelf();
        $this->assertTrue($this->repositoryModel->delete($this->paymentTokenMock));
        $this->assertFalse($this->paymentTokenMock->getIsActive());

        $this->paymentTokenMock->setPublicHash('');
        $this->assertFalse($this->repositoryModel->delete($this->paymentTokenMock));
        $this->assertFalse($this->paymentTokenMock->getIsActive());
    }

    public function testRepositorySave()
    {
        $this->resourceModelMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();
        $this->assertSame($this->paymentTokenMock, $this->repositoryModel->save($this->paymentTokenMock));
    }
}
