<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model\StoreSwitcher;

use Magento\Framework\Encryption\Encryptor;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcher\ContextInterface;
use Magento\Store\Model\StoreSwitcher\RedirectDataInterface;
use Magento\Store\Model\StoreSwitcher\RedirectDataValidator;
use PHPUnit\Framework\TestCase;

class RedirectDataValidatorTest extends TestCase
{
    /**
     * @var RedirectDataValidator
     */
    private $model;
    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $encryptor = $this->createMock(Encryptor::class);
        $this->model = new RedirectDataValidator(
            $encryptor
        );
        $store1 = $this->createConfiguredMock(
            StoreInterface::class,
            [
                'getCode' => 'en',
                'getId' => 1,
            ]
        );
        $store2 = $this->createConfiguredMock(
            StoreInterface::class,
            [
                'getCode' => 'fr',
                'getId' => 2,
            ]
        );
        $this->context = $this->createConfiguredMock(
            ContextInterface::class,
            [
                'getFromStore' => $store2,
                'getTargetStore' => $store1,
            ]
        );
        $encryptor->method('validateHash')
            ->willReturnCallback(
                function (string $value, string $hash) {
                    // phpcs:ignore Magento2.Security.InsecureFunction
                    return md5($value) === $hash;
                }
            );
    }

    /**
     * @param array $params
     * @param bool $result
     * @dataProvider validationDataProvider
     */
    public function testValidation(array $params, bool $result): void
    {
        $originalData = '{"customer_id":1}';
        $timestamp = time() - $params['elapsedTime'];
        $fromStoreCode = $params['fromStoreCode'] ?? $this->context->getFromStore()->getCode();
        $targetStoreCode = $params['targetStoreCode'] ?? $this->context->getTargetStore()->getCode();
        // phpcs:ignore Magento2.Security.InsecureFunction
        $signature = md5("{$originalData},{$timestamp},{$fromStoreCode},{$targetStoreCode}");
        $redirectData = $this->createConfiguredMock(
            RedirectDataInterface::class,
            [
                'getTimestamp' => $params['timestamp'] ?? $timestamp,
                'getData' => $params['data'] ?? $originalData,
                'getSignature' => $params['signature'] ?? $signature,
            ]
        );
        $this->assertEquals($result, $this->model->validate($this->context, $redirectData));
    }

    /**
     * @return array
     */
    public static function validationDataProvider(): array
    {
        return [
            [
                [
                    'elapsedTime' => 1,
                ],
                true
            ],
            [
                [
                    'elapsedTime' => 6,
                ],
                false
            ],
            [
                [
                    'elapsedTime' => 1,
                    'data' => '{"customer_id":2}'
                ],
                false
            ],
            [
                [
                    'elapsedTime' => 1,
                    'fromStoreCode' => 'es'

                ],
                false
            ],
            [
                [
                    'elapsedTime' => 1,
                    'targetStoreCode' => 'de'

                ],
                false
            ],
            [
                [
                    'elapsedTime' => 1,
                    'signature' => 'abcd1efgh2ijkl3mnop4qrst5uvwx6yz'

                ],
                false
            ]
        ];
    }
}
