<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\Unit\Model;

use Magento\CheckoutAgreements\Model\CheckoutAgreementsRepository;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CheckoutAgreementsRepositoryTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->factoryMock = $this->getMock(
            'Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->resourceMock = $this->getMock(
            'Magento\CheckoutAgreements\Model\ResourceModel\Agreement',
            [],
            [],
            '',
            false
        );
        $this->agrFactoryMock = $this->getMock(
            'Magento\CheckoutAgreements\Model\AgreementFactory',
            ['create'],
            [],
            '',
            false
        );
        $methods = ['addData', 'getData', 'setStores', 'getAgreementId', 'getId'];
        $this->agreementMock =
            $this->getMock('\Magento\CheckoutAgreements\Model\Agreement', $methods, [], '', false);
        $this->storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $this->extensionAttributesJoinProcessorMock = $this->getMock(
            '\Magento\Framework\Api\ExtensionAttribute\JoinProcessor',
            ['process'],
            [],
            '',
            false
        );

        $this->model = new \Magento\CheckoutAgreements\Model\CheckoutAgreementsRepository(
            $this->factoryMock,
            $this->storeManagerMock,
            $this->scopeConfigMock,
            $this->resourceMock,
            $this->agrFactoryMock,
            $this->extensionAttributesJoinProcessorMock
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
        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with($this->isInstanceOf('Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection'));

        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with('checkout/options/enable_agreements', ScopeInterface::SCOPE_STORE, null)
            ->will($this->returnValue(true));

        $storeId = 1;
        $storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $storeMock->expects($this->any())->method('getId')->will($this->returnValue($storeId));
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));

        $collectionMock = $this->objectManager->getCollectionMock(
            'Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection',
            [$this->agreementMock]
        );
        $this->factoryMock->expects($this->once())->method('create')->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())->method('addStoreFilter')->with($storeId);
        $collectionMock->expects($this->once())->method('addFieldToFilter')->with('is_active', 1);

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
