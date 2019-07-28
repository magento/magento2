<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\Unit\Model;

use Magento\CheckoutAgreements\Model\CheckoutAgreementsRepository;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckoutAgreementsRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CheckoutAgreementsRepository
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $factoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $agrFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $agreementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionAttributesJoinProcessorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $agreementsListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $agreementsFilterMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->factoryMock = $this->createPartialMock(
            \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory::class,
            ['create']
        );
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->resourceMock = $this->createMock(\Magento\CheckoutAgreements\Model\ResourceModel\Agreement::class);
        $this->agrFactoryMock = $this->createPartialMock(
            \Magento\CheckoutAgreements\Model\AgreementFactory::class,
            ['create']
        );
        $methods = ['addData', 'getData', 'setStores', 'getAgreementId', 'getId'];
        $this->agreementMock =
            $this->createPartialMock(\Magento\CheckoutAgreements\Model\Agreement::class, $methods);
        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->extensionAttributesJoinProcessorMock = $this->createPartialMock(
            \Magento\Framework\Api\ExtensionAttribute\JoinProcessor::class,
            ['process']
        );

        $this->agreementsListMock = $this->createMock(
            \Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface::class
        );
        $this->agreementsFilterMock = $this->createMock(
            \Magento\CheckoutAgreements\Model\Api\SearchCriteria\ActiveStoreAgreementsFilter::class
        );

        $this->model = new \Magento\CheckoutAgreements\Model\CheckoutAgreementsRepository(
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
            ->will($this->returnValue(false));
        $this->factoryMock->expects($this->never())->method('create');
        $this->assertEmpty($this->model->getList());
    }

    public function testGetListReturnsTheListOfActiveCheckoutAgreements()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with('checkout/options/enable_agreements', ScopeInterface::SCOPE_STORE, null)
            ->will($this->returnValue(true));

        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
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

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSaveWithException()
    {
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

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function testDeleteByIdWithException()
    {
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
