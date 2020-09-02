<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CheckoutAgreements\Test\Unit\Model;

use Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface;
use Magento\CheckoutAgreements\Model\Agreement as AgreementModel;
use Magento\CheckoutAgreements\Model\AgreementFactory;
use Magento\CheckoutAgreements\Model\Api\SearchCriteria\ActiveStoreAgreementsFilter;
use Magento\CheckoutAgreements\Model\CheckoutAgreementsRepository;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessor;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckoutAgreementsRepositoryTest extends TestCase
{
    /**
     * @var CheckoutAgreementsRepository
     */
    private $model;

    /**
     * @var MockObject
     */
    private $factoryMock;

    /**
     * @var MockObject
     */
    private $storeManagerMock;

    /**
     * @var MockObject
     */
    private $scopeConfigMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var MockObject
     */
    private $resourceMock;

    /**
     * @var MockObject
     */
    private $agrFactoryMock;

    /**
     * @var MockObject
     */
    private $agreementMock;

    /**
     * @var MockObject
     */
    private $storeMock;

    /**
     * @var MockObject
     */
    protected $extensionAttributesJoinProcessorMock;

    /**
     * @var MockObject
     */
    private $agreementsListMock;

    /**
     * @var MockObject
     */
    private $agreementsFilterMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->factoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->resourceMock = $this->createMock(Agreement::class);
        $this->agrFactoryMock = $this->createPartialMock(
            AgreementFactory::class,
            ['create']
        );
        $this->agreementMock = $this->getMockBuilder(AgreementModel::class)
            ->addMethods(['setStores'])
            ->onlyMethods(['addData', 'getData', 'getAgreementId', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->createMock(Store::class);
        $this->extensionAttributesJoinProcessorMock = $this->createPartialMock(
            JoinProcessor::class,
            ['process']
        );

        $this->agreementsListMock = $this->createMock(
            CheckoutAgreementsListInterface::class
        );
        $this->agreementsFilterMock = $this->createMock(
            ActiveStoreAgreementsFilter::class
        );

        $this->model = new CheckoutAgreementsRepository(
            $this->factoryMock,
            $this->storeManagerMock,
            $this->scopeConfigMock,
            $this->resourceMock,
            $this->agrFactoryMock,
            $this->extensionAttributesJoinProcessorMock,
            $this->agreementsListMock,
            $this->agreementsFilterMock
        );
    }

    public function testGetListReturnsEmptyListIfCheckoutAgreementsAreDisabledOnFrontend()
    {
        $this->extensionAttributesJoinProcessorMock->expects($this->never())
            ->method('process');
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with('checkout/options/enable_agreements', ScopeInterface::SCOPE_STORE, null)
            ->willReturn(false);
        $this->factoryMock->expects($this->never())->method('create');
        $this->assertEmpty($this->model->getList());
    }

    public function testGetListReturnsTheListOfActiveCheckoutAgreements()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with('checkout/options/enable_agreements', ScopeInterface::SCOPE_STORE, null)
            ->willReturn(true);

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->agreementsFilterMock->expects($this->once())
            ->method('buildSearchCriteria')
            ->willReturn($searchCriteriaMock);

        $this->agreementsListMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn([$this->agreementMock]);
        $this->assertEquals([$this->agreementMock], $this->model->getList());
    }

    public function testSave()
    {
        $this->agreementMock->expects($this->once())->method('getAgreementId')->willReturn(null);
        $this->agrFactoryMock->expects($this->never())->method('create');
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn('storeId');
        $this->agreementMock->expects($this->once())->method('setStores');
        $this->resourceMock->expects($this->once())->method('save')->with($this->agreementMock);
        $this->model->save($this->agreementMock);
    }

    public function testUpdate()
    {
        $agreementId = 1;
        $this->agreementMock->expects($this->once())->method('getAgreementId')->willReturn($agreementId);
        $this->agrFactoryMock->expects($this->once())->method('create')->willReturn($this->agreementMock);
        $this->resourceMock
            ->expects($this->once())
            ->method('load')
            ->with($this->agreementMock, $agreementId);
        $this->storeManagerMock->expects($this->never())->method('getStore');
        $this->agreementMock->expects($this->once())->method('setStores');
        $this->agreementMock->expects($this->once())->method('getId')->willReturn($agreementId);
        $this->agreementMock->expects($this->any())->method('getData')->willReturn(['data']);
        $this->agreementMock
            ->expects($this->once())
            ->method('addData')->with(['data'])
            ->willReturn($this->agreementMock);
        $this->resourceMock->expects($this->once())->method('save')->with($this->agreementMock);
        $this->assertEquals($this->agreementMock, $this->model->save($this->agreementMock, 1));
    }

    public function testSaveWithException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $this->agreementMock->expects($this->exactly(2))->method('getAgreementId')->willReturn(null);
        $this->agrFactoryMock->expects($this->never())->method('create');
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn('storeId');
        $this->agreementMock->expects($this->once())->method('setStores');
        $this->resourceMock
            ->expects($this->once())
            ->method('save')
            ->with($this->agreementMock)->willThrowException(new \Exception());
        $this->model->save($this->agreementMock);
    }

    public function testDeleteById()
    {
        $agreementId = 1;
        $this->agrFactoryMock->expects($this->once())->method('create')->willReturn($this->agreementMock);
        $this->resourceMock
            ->expects($this->once())
            ->method('load')
            ->with($this->agreementMock, $agreementId)
            ->willReturn($this->agreementMock);
        $this->agreementMock->expects($this->once())->method('getId')->willReturn($agreementId);
        $this->resourceMock->expects($this->once())->method('delete');
        $this->assertTrue($this->model->deleteById(1));
    }

    public function testDeleteByIdWithException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotDeleteException');
        $agreementId = 1;
        $this->agrFactoryMock->expects($this->once())->method('create')->willReturn($this->agreementMock);
        $this->resourceMock
            ->expects($this->once())
            ->method('load')
            ->with($this->agreementMock, $agreementId)
            ->willReturn($this->agreementMock);
        $this->agreementMock->expects($this->once())->method('getId')->willReturn($agreementId);
        $this->resourceMock->expects($this->once())->method('delete')->willThrowException(new \Exception());
        $this->assertTrue($this->model->deleteById(1));
    }
}
