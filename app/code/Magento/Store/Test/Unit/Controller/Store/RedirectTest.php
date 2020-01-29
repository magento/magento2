<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Controller\Store;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Controller\Store\Redirect;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreResolver;
use Magento\Store\Model\StoreSwitcher\HashGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for redirect controller
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RedirectTest extends TestCase
{
    private const DEFAULT_STORE_VIEW_CODE = 'default';
    private const STORE_CODE = 'sv1';

    /**
     * @var StoreRepositoryInterface|MockObject
     */
    private $storeRepositoryMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var StoreResolverInterface|MockObject
     */
    private $storeResolverMock;

    /**
     * @var RedirectInterface|MockObject
     */
    private $redirectMock;

    /**
     * @var ResponseInterface|MockObject
     */
    private $responseMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var Store|MockObject
     */
    private $formStoreMock;

    /**
     * @var Store|MockObject
     */
    private $currentStoreMock;

    /**
     * @var SidResolverInterface|MockObject
     */
    private $sidResolverMock;

    /**
     * @var HashGenerator|MockObject
     */
    private $hashGeneratorMock;

    /**
     * @var Redirect
     */
    private $redirectController;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();
        $this->redirectMock = $this->getMockBuilder(RedirectInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['redirect'])
            ->getMockForAbstractClass();
        $this->storeResolverMock = $this->getMockBuilder(StoreResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentStoreId'])
            ->getMockForAbstractClass();
        $this->storeRepositoryMock = $this->getMockBuilder(StoreRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getById', 'get'])
            ->getMockForAbstractClass();
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['addErrorMessage'])
            ->getMockForAbstractClass();
        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->formStoreMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCode'])
            ->getMockForAbstractClass();
        $this->sidResolverMock = $this->getMockBuilder(SidResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUseSessionInUrl'])
            ->getMockForAbstractClass();
        $this->hashGeneratorMock = $this->createMock(HashGenerator::class);

        $this->currentStoreMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseUrl'])
            ->getMock();
        $this->storeRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->willReturn($this->currentStoreMock);
        $this->storeResolverMock
            ->expects($this->once())
            ->method('getCurrentStoreId')
            ->willReturnSelf();

        $objectManager = new ObjectManagerHelper($this);
        $context = $objectManager->getObject(
            Context::class,
            [
                '_request' => $this->requestMock,
                '_redirect' => $this->redirectMock,
                '_response' => $this->responseMock,
                'messageManager' => $this->messageManagerMock,
            ]
        );
        $this->redirectController = $objectManager->getObject(
            Redirect::class,
            [
                'storeRepository' => $this->storeRepositoryMock,
                'storeResolver' => $this->storeResolverMock,
                'sidResolver' => $this->sidResolverMock,
                'hashGenerator' => $this->hashGeneratorMock,
                'context' => $context,
            ]
        );
    }

    /**
     * Verify redirect controller
     *
     * @param string $defaultStoreViewCode
     * @param string $storeCode
     *
     * @dataProvider getConfigDataProvider
     * @return void
     */
    public function testRedirect(string $defaultStoreViewCode, string $storeCode): void
    {
        $this->requestMock
            ->expects($this->exactly(3))
            ->method('getParam')
            ->withConsecutive(
                [StoreResolver::PARAM_NAME],
                ['___from_store'],
                [ActionInterface::PARAM_NAME_URL_ENCODED]
            )->willReturnOnConsecutiveCalls(
                $storeCode,
                $defaultStoreViewCode,
                $defaultStoreViewCode
            );
        $this->storeRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($defaultStoreViewCode)
            ->willReturn($this->formStoreMock);
        $this->formStoreMock
            ->expects($this->once())
            ->method('getCode')
            ->willReturn($defaultStoreViewCode);
        $this->sidResolverMock
            ->expects($this->once())
            ->method('getUseSessionInUrl')
            ->willReturn(false);
        $this->hashGeneratorMock
            ->expects($this->once())
            ->method('generateHash')
            ->with($this->formStoreMock)
            ->willReturn([]);
        $this->redirectMock
            ->expects($this->once())
            ->method('redirect')
            ->with(
                $this->responseMock,
                'stores/store/switch',
                ['_nosid' => true,
                    '_query' => [
                        'uenc' => $defaultStoreViewCode,
                        '___from_store' => $defaultStoreViewCode,
                        '___store' => $storeCode
                    ]
                ]
            );

        $this->assertEquals(null, $this->redirectController->execute());
    }

    /**
     *  Verify execute with exception
     *
     * @param string $defaultStoreViewCode
     * @param string $storeCode
     * @return void
     * @dataProvider getConfigDataProvider
     */
    public function testRedirectWithThrowsException(string $defaultStoreViewCode, string $storeCode): void
    {
        $this->requestMock
            ->expects($this->exactly(2))
            ->method('getParam')
            ->withConsecutive(
                [StoreResolver::PARAM_NAME],
                ['___from_store']
            )->willReturnOnConsecutiveCalls(
                $storeCode,
                $defaultStoreViewCode
            );
        $this->storeRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($defaultStoreViewCode)
            ->willThrowException(new NoSuchEntityException());
        $this->messageManagerMock
            ->expects($this->once())
            ->method('addErrorMessage')
            ->with("Requested store is not found ({$defaultStoreViewCode})")
            ->willReturnSelf();
        $this->currentStoreMock
            ->expects($this->once())
            ->method('getBaseUrl')
            ->willReturnSelf();
        $this->redirectMock
            ->expects($this->once())
            ->method('redirect')
            ->with($this->responseMock, $this->currentStoreMock)
            ->willReturnSelf();

        $this->assertEquals(null, $this->redirectController->execute());
    }

    /**
     * Verify redirect target is null
     *
     * @return void
     */
    public function testRedirectTargetIsNull(): void
    {
        $this->requestMock
            ->expects($this->exactly(2))
            ->method('getParam')
            ->withConsecutive(
                [StoreResolver::PARAM_NAME],
                ['___from_store']
            )->willReturnOnConsecutiveCalls(
                null,
                null
            );
        $this->storeRepositoryMock
            ->expects($this->never())
            ->method('get');

        $this->assertEquals($this->responseMock, $this->redirectController->execute());
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function getConfigDataProvider(): array
    {
        return [
            [self::DEFAULT_STORE_VIEW_CODE, self::STORE_CODE]
        ];
    }
}
