<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Model;

use Magento\Framework\Api\SearchCriteria;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Vault\Model\PaymentTokenFactory;
use Magento\Vault\Model\VaultPaymentInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\PaymentTokenNullRepository;
use Magento\Vault\Model\PaymentTokenRepositoryProxy;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Model\Adminhtml\Source\VaultProvidersMap;
use Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface;
use Magento\Vault\Api\Data\PaymentTokenSearchResultsInterfaceFactory;

/**
 * Class PaymentTokenRepositoryProxyTest
 *
 * @see \Magento\Vault\Model\PaymentTokenRepositoryProxy
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentTokenRepositoryProxyTest extends \PHPUnit_Framework_TestCase
{
    const DELETE_RESULT = 'delete_result';

    const SAVE_RESULT = 'save_result';

    /**
     * @var PaymentTokenNullRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $nullRepositoryMock;

    /**
     * @var VaultPaymentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $vaultPaymentMock;

    /**
     * @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->nullRepositoryMock = $this->getMockBuilder(PaymentTokenNullRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->vaultPaymentMock = $this->getMockBuilder(VaultPaymentInterface::class)
            ->getMockForAbstractClass();
        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
    }

    /**
     * Run test for proxy methods
     */
    public function testProxyCall()
    {
        /** @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject $searchCriteriaMock */
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->getMockForAbstractClass();

        /** @var PaymentTokenSearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject $searchResultMock */
        $searchResultMock = $this->getMockBuilder(PaymentTokenSearchResultsInterface::class)
            ->getMockForAbstractClass();

        /** @var PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject $tokenMock */
        $tokenMock = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMockForAbstractClass();

        /** @var PaymentTokenRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject $proxyCallsMock */
        $proxyCallsMock = $this->getMockBuilder(PaymentTokenRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->vaultPaymentMock->expects(self::once())
            ->method('isActive')
            ->willReturn(1);

        $this->configMock->expects(self::once())
            ->method('getValue')
            ->with(VaultProvidersMap::VALUE_CODE)
            ->willReturn('code');

        $this->objectManagerMock->expects(self::once())
            ->method('get')
            ->with(get_class($proxyCallsMock))
            ->willReturn($proxyCallsMock);

        $proxy = new PaymentTokenRepositoryProxy(
            $this->nullRepositoryMock,
            $this->vaultPaymentMock,
            $this->configMock,
            $this->objectManagerMock,
            ['code' => get_class($proxyCallsMock)]
        );

        $proxyCallsMock->expects(self::once())
            ->method('delete')
            ->with($tokenMock)
            ->willReturn(self::DELETE_RESULT);
        $proxyCallsMock->expects(self::once())
            ->method('getById')
            ->with(1)
            ->willReturn($tokenMock);
        $proxyCallsMock->expects(self::once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultMock);
        $proxyCallsMock->expects(self::once())
            ->method('save')
            ->with($tokenMock)
            ->willReturn(self::SAVE_RESULT);

        self::assertEquals(self::DELETE_RESULT, $proxy->delete($tokenMock));
        self::assertEquals($tokenMock, $proxy->getById(1));
        self::assertEquals($searchResultMock, $proxy->getList($searchCriteriaMock));
        self::assertEquals(self::SAVE_RESULT, $proxy->save($tokenMock));
    }

    /**
     * Run test for null repository
     */
    public function testNullRepository()
    {
        /** @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject $searchCriteriaMock */
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->getMockForAbstractClass();
        /** @var PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject $tokenMock */
        $tokenMock = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMockForAbstractClass();
        /** @var PaymentTokenSearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject $searchResultMock */
        $searchResultMock = $this->getMockBuilder(PaymentTokenSearchResultsInterface::class)
            ->getMockForAbstractClass();

        $this->objectManagerMock->expects(self::never())
            ->method('get');

        $proxy = new PaymentTokenRepositoryProxy(
            $this->nullRepositoryMock,
            $this->vaultPaymentMock,
            $this->configMock,
            $this->objectManagerMock,
            []
        );

        $this->nullRepositoryMock->expects(self::once())
            ->method('getById')
            ->with(1)
            ->willReturn($tokenMock);
        $this->nullRepositoryMock->expects(self::once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultMock);

        self::assertEquals($tokenMock, $proxy->getById(1));
        self::assertEquals($searchResultMock, $proxy->getList($searchCriteriaMock));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessageRegExp You must implement this operation\. \([^:]+::save\)
     */
    public function testNullRepositoryExceptionSave()
    {
        /** @var PaymentTokenFactory|\PHPUnit_Framework_MockObject_MockObject $tokenFactoryMock */
        $tokenFactoryMock = $this->getMockBuilder(PaymentTokenFactory::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        /** @var PaymentTokenSearchResultsInterfaceFactory
         * |\PHPUnit_Framework_MockObject_MockObject $searchResultFactoryMock */
        $searchResultFactoryMock = $this->getMockBuilder(PaymentTokenSearchResultsInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        /** @var PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject $tokenMock */
        $tokenMock = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMockForAbstractClass();

        $proxy = new PaymentTokenRepositoryProxy(
            new \Magento\Vault\Model\PaymentTokenNullRepository(
                $tokenFactoryMock,
                $searchResultFactoryMock
            ),
            $this->vaultPaymentMock,
            $this->configMock,
            $this->objectManagerMock,
            []
        );

        $proxy->save($tokenMock);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessageRegExp You must implement this operation\. \([^:]+::delete\)
     */
    public function testNullRepositoryExceptionDelete()
    {
        /** @var PaymentTokenFactory|\PHPUnit_Framework_MockObject_MockObject $tokenFactoryMock */
        $tokenFactoryMock = $this->getMockBuilder(PaymentTokenFactory::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        /** @var PaymentTokenSearchResultsInterfaceFactory
         * |\PHPUnit_Framework_MockObject_MockObject $searchResultFactoryMock */
        $searchResultFactoryMock = $this->getMockBuilder(PaymentTokenSearchResultsInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        /** @var PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject $tokenMock */
        $tokenMock = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMockForAbstractClass();

        $proxy = new PaymentTokenRepositoryProxy(
            new \Magento\Vault\Model\PaymentTokenNullRepository(
                $tokenFactoryMock,
                $searchResultFactoryMock
            ),
            $this->vaultPaymentMock,
            $this->configMock,
            $this->objectManagerMock,
            []
        );

        $proxy->delete($tokenMock);
    }
}
