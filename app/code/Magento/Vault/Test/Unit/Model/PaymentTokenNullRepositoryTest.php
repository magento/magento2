<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Model;

use Magento\Framework\Api\SearchCriteria;
use Magento\Vault\Model\PaymentTokenFactory;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\PaymentTokenNullRepository;
use Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface;
use Magento\Vault\Api\Data\PaymentTokenSearchResultsInterfaceFactory;

/**
 * Class PaymentTokenNullRepositoryTest
 *
 * @see \Magento\Vault\Model\PaymentTokenNullRepository
 */
class PaymentTokenNullRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PaymentTokenFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenFactoryMock;

    /**
     * @var PaymentTokenSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsFactoryMock;

    /**
     * @var PaymentTokenNullRepository
     */
    private $repository;

    /**
     * Set up
     */
    protected function setUp()
    {
        /** @var PaymentTokenFactory|\PHPUnit_Framework_MockObject_MockObject $tokenFactoryMock */
        $this->tokenFactoryMock = $this->getMockBuilder(PaymentTokenFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        /** @var PaymentTokenSearchResultsInterfaceFactory
         * |\PHPUnit_Framework_MockObject_MockObject $searchResultFactoryMock */
        $this->searchResultsFactoryMock = $this->getMockBuilder(PaymentTokenSearchResultsInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->repository = new PaymentTokenNullRepository(
            $this->tokenFactoryMock,
            $this->searchResultsFactoryMock
        );
    }

    /**
     * Run test fot getList method
     */
    public function testGetList()
    {
        /** @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject $searchCriteriaMock */
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->getMockForAbstractClass();
        /** @var PaymentTokenSearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject $searchResultMock */
        $searchResultMock = $this->getMockBuilder(PaymentTokenSearchResultsInterface::class)
            ->getMockForAbstractClass();

        $this->searchResultsFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($searchResultMock);

        $searchResultMock->expects(self::once())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock);
        $searchResultMock->expects(self::once())
            ->method('setItems')
            ->with([]);

        self::assertEquals($searchResultMock, $this->repository->getList($searchCriteriaMock));
    }

    /**
     * Run test for getById method
     */
    public function testGetById()
    {
        /** @var PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject $tokenMock */
        $tokenMock = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMockForAbstractClass();

        $this->tokenFactoryMock->expects(self::once())
            ->method('create')
            ->willReturn($tokenMock);

        self::assertEquals($tokenMock, $this->repository->getById(1));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessageRegExp You must implement this operation\. \([^:]+::save\)
     */
    public function testNullRepositoryExceptionSave()
    {
        /** @var PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject $tokenMock */
        $tokenMock = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMockForAbstractClass();

        $this->repository->save($tokenMock);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessageRegExp You must implement this operation\. \([^:]+::delete\)
     */
    public function testNullRepositoryExceptionDelete()
    {
        /** @var PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject $tokenMock */
        $tokenMock = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMockForAbstractClass();

        $this->repository->delete($tokenMock);
    }
}
