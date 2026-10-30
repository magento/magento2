<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
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
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreResolver;
use Magento\Store\Model\StoreSwitcher\ContextInterface;
use Magento\Store\Model\StoreSwitcher\ContextInterfaceFactory;
use Magento\Store\Model\StoreSwitcher\HashGenerator;
use Magento\Store\Model\StoreSwitcher\RedirectDataGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for redirect controller
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RedirectTest extends TestCase
{
    /**
     * Stub for default store view code
     */
    private const STUB_DEFAULT_STORE_VIEW_CODE = 'default';

    /**
     * Stub for default store code
     */
    private const STUB_STORE_CODE = 'sv1';

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

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
    private $fromStoreMock;

    /**
     * @var Store|MockObject
     */
    private $targetStoreMock;

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
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->redirectMock = $this->createMock(RedirectInterface::class);
        $this->storeResolverMock = $this->createMock(StoreResolverInterface::class);
        $this->storeRepositoryMock = $this->createMock(StoreRepositoryInterface::class);
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);
        $this->fromStoreMock = $this->createMock(Store::class);
        $this->targetStoreMock = $this->createMock(Store::class);
        $this->sidResolverMock = $this->createMock(SidResolverInterface::class);
        $this->hashGeneratorMock = $this->createMock(HashGenerator::class);

        $this->currentStoreMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBaseUrl'])
            ->getMock();
        $this->storeRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->willReturn($this->currentStoreMock);
        $this->storeResolverMock
            ->expects($this->once())
            ->method('getCurrentStoreId')
            ->willReturnSelf();

        $redirectDataGenerator = $this->createMock(RedirectDataGenerator::class);
        $contextFactory = $this->createMock(ContextInterfaceFactory::class);
        $contextFactory->method('create')
            ->willReturn($this->createMock(ContextInterface::class));

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
        $this->model = $objectManager->getObject(
            Redirect::class,
            [
                'storeManager' => $this->storeManagerMock,
                'storeRepository' => $this->storeRepositoryMock,
                'storeResolver' => $this->storeResolverMock,
                'sidResolver' => $this->sidResolverMock,
                'hashGenerator' => $this->hashGeneratorMock,
                'context' => $context,
                'redirectDataGenerator' => $redirectDataGenerator,
                'contextFactory' => $contextFactory,
            ]
        );
    }

    /**
     * Verify redirect controller
     *
     * @param string $defaultStoreViewCode
     * @param string $storeCode
     *
     * @return void
     */
    #[DataProvider('getConfigDataProvider')]
    public function testRedirect(string $defaultStoreViewCode, string $storeCode): void
    {
        $this->requestMock
            ->expects($this->exactly(3))
            ->method('getParam')
            ->willReturnCallback(
                function ($param) use ($storeCode, $defaultStoreViewCode) {
                    if ($param === StoreResolver::PARAM_NAME) {
                        return $storeCode;
                    } elseif ($param === '___from_store') {
                        return $defaultStoreViewCode;
                    } elseif ($param === ActionInterface::PARAM_NAME_URL_ENCODED) {
                        return $defaultStoreViewCode;
                    }
                }
            );
        $this->storeRepositoryMock
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                [$defaultStoreViewCode, $this->fromStoreMock],
                [$storeCode, $this->targetStoreMock],
            ]);
        $this->fromStoreMock
            ->expects($this->once())
            ->method('getCode')
            ->willReturn($defaultStoreViewCode);
        $this->storeManagerMock
            ->expects($this->once())
            ->method('setCurrentStore')
            ->with($this->targetStoreMock);
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
                        '___store' => $storeCode,
                        'data' => '',
                        'time_stamp' => 0,
                        'signature' => '',
                    ]
                ]
            );

        $this->assertNull($this->model->execute());
    }

    /**
     *  Verify execute with exception
     *
     * @param string $defaultStoreViewCode
     * @param string $storeCode
     * @return void
     */
    #[DataProvider('getConfigDataProvider')]
    public function testRedirectWithThrowsException(string $defaultStoreViewCode, string $storeCode): void
    {
        $this->requestMock
            ->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnCallback(
                function ($param) use ($storeCode, $defaultStoreViewCode) {
                    if ($param === StoreResolver::PARAM_NAME) {
                        return $storeCode;
                    } elseif ($param === '___from_store') {
                        return $defaultStoreViewCode;
                    }
                }
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

        $this->assertNull($this->model->execute());
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
            ->willReturnCallback(
                function ($param) {
                    if ($param === StoreResolver::PARAM_NAME || $param === '___from_store') {
                        return null;
                    }
                }
            );
        $this->storeRepositoryMock
            ->expects($this->never())
            ->method('get');

        $this->assertEquals($this->responseMock, $this->model->execute());
    }

    /**
     * Data provider
     *
     * @return array
     */
    public static function getConfigDataProvider(): array
    {
        return [
            [self::STUB_DEFAULT_STORE_VIEW_CODE, self::STUB_STORE_CODE]
        ];
    }
}
