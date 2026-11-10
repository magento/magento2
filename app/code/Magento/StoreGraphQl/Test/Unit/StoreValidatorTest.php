<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Test\Unit;

use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\StoreGraphQl\Controller\HttpRequestValidator\StoreValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for StoreValidator class
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreValidatorTest extends TestCase
{
    use MockCreationTrait;

    private const DEFAULT_STORE_VIEW_CODE = 'default';
    private const STORE_CODE = 'sv1';

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var HttpRequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var StoreValidator
     */
    private $storeValidator;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->requestMock = $this->createPartialMockWithReflection(
            HttpRequestInterface::class,
            ['getHeader', 'isPost', 'isGet', 'isPatch', 'isDelete', 'isPut', 'isAjax']
        );

        $objectManager = new ObjectManagerHelper($this);

        $this->storeValidator = $objectManager->getObject(
            StoreValidator::class,
            [
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    /**
     * Verify validate
     *
     * @param array $config
     *
     * @throws GraphQlInputException
     */
    #[DataProvider('getConfigDataProvider')]
    public function testValidate(array $config): void
    {
        $this->requestMock
            ->expects($this->once())
            ->method('getHeader')
            ->with('Store')
            ->willReturn($config['store']);
        $this->storeManagerMock
            ->expects($this->once())
            ->method('getStores')
            ->with(false, true)
            ->willReturn($config['store']);
        $this->storeManagerMock
            ->expects($this->once())
            ->method('setCurrentStore')
            ->with(null)
            ->willReturnSelf();
        $this->expectExceptionMessage('Requested store is not found (sv1)');
        $this->storeValidator->validate($this->requestMock);
    }

    /**
     * Verify the error message stays valid UTF-8 when the header contains invalid UTF-8
     *
     * @param string $header
     * @param string $expectedMessage
     */
    #[DataProvider('unknownStoreCodeProvider')]
    public function testValidateUnknownStoreCodeProducesValidUtf8Message(string $header, string $expectedMessage): void
    {
        $this->requestMock
            ->expects($this->once())
            ->method('getHeader')
            ->with('Store')
            ->willReturn($header);
        $this->storeManagerMock
            ->expects($this->once())
            ->method('getStores')
            ->with(false, true)
            ->willReturn([]);
        $this->storeManagerMock
            ->expects($this->once())
            ->method('setCurrentStore')
            ->with(null);

        try {
            $this->storeValidator->validate($this->requestMock);
            $this->fail('Expected ' . GraphQlInputException::class);
        } catch (GraphQlInputException $e) {
            $this->assertSame($expectedMessage, $e->getMessage());
            $this->assertTrue(mb_check_encoding($e->getMessage(), 'UTF-8'));
        }
    }

    /**
     * @return array
     */
    public static function unknownStoreCodeProvider(): array
    {
        return [
            'valid utf-8 is echoed unchanged' => ["caf\u{E9}", "Requested store is not found (caf\u{E9})"],
            'invalid utf-8 is scrubbed' => [
                "\xC3\x28",
                'Requested store is not found (' . mb_scrub("\xC3\x28", 'UTF-8') . ')',
            ],
        ];
    }

    /**
     * Verify validate with active store
     *
     * @param array $config
     *
     * @throws GraphQlInputException
     */
    #[DataProvider('getConfigDataProvider')]
    public function testValidateWithStoreActive(array $config): void
    {
        $this->requestMock
            ->expects($this->once())
            ->method('getHeader')
            ->with('Store')
            ->willReturn($config['default']);
        $this->storeManagerMock
            ->expects($this->once())
            ->method('getStores')
            ->with(false, true)
            ->willReturn($config['default']);
        $this->storeManagerMock
            ->expects($this->never())
            ->method('setCurrentStore')
            ->with(null)
            ->willReturnSelf();
        $this->storeValidator->validate($this->requestMock);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public static function getConfigDataProvider(): array
    {
        return [
            [
                [
                    'default'   =>  self::DEFAULT_STORE_VIEW_CODE,
                    'store'     =>  self::STORE_CODE
                ]
            ]
        ];
    }
}
