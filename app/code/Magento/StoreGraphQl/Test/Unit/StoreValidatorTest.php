<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Test\Unit;

use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\StoreGraphQl\Controller\HttpRequestValidator\StoreValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for StoreValidator class
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreValidatorTest extends TestCase
{
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
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->requestMock = $this->getMockBuilder(HttpRequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'isPost',
                    'isGet',
                    'isPatch',
                    'isDelete',
                    'isPut',
                    'isAjax',
                    'getHeader'
                ]
            )
            ->getMockForAbstractClass();

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
     * @dataProvider getConfigDataProvider
     * @throws GraphQlInputException
     */
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
     * Verify validate with active store
     *
     * @param array $config
     *
     * @throws GraphQlInputException
     * @dataProvider getConfigDataProvider
     */
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
    public function getConfigDataProvider(): array
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
