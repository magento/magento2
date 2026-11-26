<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model;

use InvalidArgumentException;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\App\Request\StorePathInfoValidator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreResolver;
use Magento\Store\Model\StoresData;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreResolverTest extends TestCase
{
    /**
     * @var StoreRepositoryInterface|MockObject
     */
    private $storeRepositoryMock;

    /**
     * @var StoreCookieManagerInterface|MockObject
     */
    private $storeCookieManagerMock;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var StoresData|MockObject
     */
    private $storesDataMock;

    /**
     * @var StorePathInfoValidator|MockObject
     */
    private $storePathInfoValidatorMock;

    /**
     * @var CookieManagerInterface|MockObject
     */
    private $cookieManagerMock;

    /**
     * @var StoreResolver
     */
    private $storeResolver;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->storeRepositoryMock = $this->createMock(StoreRepositoryInterface::class);
        $this->storeCookieManagerMock = $this->createMock(StoreCookieManagerInterface::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->storesDataMock = $this->createMock(StoresData::class);
        $this->storePathInfoValidatorMock = $this->createMock(StorePathInfoValidator::class);
        $this->cookieManagerMock = $this->createMock(CookieManagerInterface::class);

        $this->storeResolver = new StoreResolver(
            $this->storeRepositoryMock,
            $this->storeCookieManagerMock,
            $this->requestMock,
            $this->storesDataMock,
            $this->storePathInfoValidatorMock,
            cookieManagerInterface: $this->cookieManagerMock
        );
    }

    /**
     * Verify invalid array store parameter throws native InvalidArgumentException with a string message.
     *
     * @param array $invalidStoreCode
     * @return void
     */
    #[DataProvider('invalidArrayStoreCodeDataProvider')]
    public function testGetCurrentStoreIdThrowsInvalidArgumentExceptionForInvalidArrayStoreParameter(
        array $invalidStoreCode
    ): void {
        $this->storesDataMock->expects($this->once())
            ->method('getStoresData')
            ->willReturn([[1], 1]);

        $this->storePathInfoValidatorMock->expects($this->once())
            ->method('getValidStoreCode')
            ->with($this->requestMock)
            ->willReturn(null);

        $this->storeCookieManagerMock->expects($this->once())
            ->method('getStoreCodeFromCookie')
            ->willReturn($invalidStoreCode);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with(StoreManagerInterface::PARAM_NAME, $invalidStoreCode)
            ->willReturn($invalidStoreCode);

        $this->storeRepositoryMock->expects($this->never())
            ->method('getActiveStoreByCode');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid store parameter.');

        $this->storeResolver->getCurrentStoreId();
    }

    /**
     * @return array
     */
    public static function invalidArrayStoreCodeDataProvider(): array
    {
        return [
            'indexed array from malformed cookie' => [['test']],
            'associative array without _data.code' => [['foo' => 'bar']],
            'empty array' => [[]],
        ];
    }

    /**
     * Verify valid array store parameter with _data.code is resolved correctly.
     *
     * @return void
     */
    public function testGetCurrentStoreIdResolvesStoreCodeFromArrayWithDataCode(): void
    {
        $storeCode = 'default';
        $storeId = 1;
        $storeMock = $this->createMock(StoreInterface::class);

        $storeMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($storeId);

        $this->storesDataMock->expects($this->once())
            ->method('getStoresData')
            ->willReturn([[$storeId], $storeId]);

        $this->storePathInfoValidatorMock->expects($this->once())
            ->method('getValidStoreCode')
            ->with($this->requestMock)
            ->willReturn(null);

        $this->storeCookieManagerMock->expects($this->once())
            ->method('getStoreCodeFromCookie')
            ->willReturn(['_data' => ['code' => $storeCode]]);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with(
                StoreManagerInterface::PARAM_NAME,
                ['_data' => ['code' => $storeCode]]
            )
            ->willReturn(['_data' => ['code' => $storeCode]]);

        $this->storeRepositoryMock->expects($this->once())
            ->method('getActiveStoreByCode')
            ->with($storeCode)
            ->willReturn($storeMock);

        $this->assertSame($storeId, $this->storeResolver->getCurrentStoreId());
    }

    /**
     * Verify invalid store code from request query parameter also throws InvalidArgumentException.
     *
     * @return void
     */
    public function testGetCurrentStoreIdThrowsInvalidArgumentExceptionForInvalidArrayRequestParameter(): void
    {
        $invalidStoreCode = ['test'];

        $this->storesDataMock->expects($this->once())
            ->method('getStoresData')
            ->willReturn([[1], 1]);

        $this->storePathInfoValidatorMock->expects($this->once())
            ->method('getValidStoreCode')
            ->with($this->requestMock)
            ->willReturn(null);

        $this->storeCookieManagerMock->expects($this->once())
            ->method('getStoreCodeFromCookie')
            ->willReturn(null);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with(StoreManagerInterface::PARAM_NAME, null)
            ->willReturn($invalidStoreCode);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid store parameter.');

        $this->storeResolver->getCurrentStoreId();
    }
}
