<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\CheckoutAgreements\Test\Unit\Model;

use Magento\CheckoutAgreements\Model\EmulateStore;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EmulateStoreTest extends TestCase
{
    /**
     * @var EmulateStore
     */
    private EmulateStore $emulateStore;

    /**
     * @var StoreManager|MockObject
     */
    private StoreManager|MockObject $storeManager;

    /**
     * @var StoreInterface|MockObject
     */
    private StoreInterface|MockObject $store;

    public function setUp(): void
    {
        $this->storeManager = $this->createMock(StoreManager::class);
        $this->store = $this->getMockForAbstractClass(StoreInterface::class);
        $this->emulateStore = new EmulateStore(
            $this->storeManager
        );
    }

    /**
     * @param int $storeId
     * @param int $currentStoreId
     * @return void
     * @throws NoSuchEntityException
     * @dataProvider executeDataProvider
     */
    public function testExecute(int $storeId, int $currentStoreId): void
    {
        $this->store->expects($this->once())
            ->method('getId')
            ->willReturn($currentStoreId);
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);

        if ($storeId !== $currentStoreId) {
            $expected = [
                [$storeId],
                [$currentStoreId]
            ];
            $this->storeManager->expects($this->exactly(2))
                ->method('setCurrentStore')
                ->willReturnCallback(function (...$args) use (&$expected) {
                    $expectedArgs = array_shift($expected);
                    $this->assertSame($expectedArgs, $args);
                });
        }

        $this->emulateStore->execute($storeId, function () {
        }, [1, 2, 3]);
    }

    /**
     * @return array[]
     */
    public function executeDataProvider(): array
    {
        return [
            'no-emulation' => ['storeId' => 1, 'currentStoreId' => 1],
            'emulation' => ['storeId' => 2, 'currentStoreId' => 1]
        ];
    }
}
