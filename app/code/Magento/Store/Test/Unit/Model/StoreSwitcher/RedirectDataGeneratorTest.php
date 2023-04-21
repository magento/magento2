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
use Magento\Store\Model\StoreSwitcher\RedirectDataGenerator;
use Magento\Store\Model\StoreSwitcher\RedirectDataInterface;
use Magento\Store\Model\StoreSwitcher\RedirectDataInterfaceFactory;
use Magento\Store\Model\StoreSwitcher\RedirectDataPreprocessorInterface;
use Magento\Store\Model\StoreSwitcher\RedirectDataSerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RedirectDataGeneratorTest extends TestCase
{
    /**
     * @var RedirectDataPreprocessorInterface|MockObject
     */
    private $preprocessor;
    /**
     * @var RedirectDataSerializerInterface|MockObject
     */
    private $dataSerializer;
    /**
     * @var ContextInterface|MockObject
     */
    private $context;
    /**
     * @var RedirectDataGenerator
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->preprocessor = $this->createMock(RedirectDataPreprocessorInterface::class);
        $this->dataSerializer = $this->createMock(RedirectDataSerializerInterface::class);
        $dataFactory = $this->createMock(RedirectDataInterfaceFactory::class);
        $encryptor = $this->createMock(Encryptor::class);
        $logger = $this->createMock(LoggerInterface::class);
        $this->model = new RedirectDataGenerator(
            $encryptor,
            $this->preprocessor,
            $this->dataSerializer,
            $dataFactory,
            $logger
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
        $encryptor->method('hash')
            ->willReturnCallback(
                function (string $arg1) {
                    // phpcs:ignore Magento2.Security.InsecureFunction
                    return md5($arg1);
                }
            );
        $dataFactory->method('create')
            ->willReturnCallback(
                function (array $data) {
                    return $this->createConfiguredMock(
                        RedirectDataInterface::class,
                        [
                            'getTimestamp' => $data['timestamp'],
                            'getData' => $data['data'],
                            'getSignature' => $data['signature'],
                        ]
                    );
                }
            );
    }

    public function testGenerate(): void
    {
        $this->preprocessor->method('process')
            ->willReturn(['customer_id' => 1]);
        $this->dataSerializer->method('serialize')
            ->willReturnCallback('json_encode');
        $redirectData = $this->model->generate($this->context);
        $time = time();
        $this->assertEqualsWithDelta($time, $redirectData->getTimestamp(), 1);
        $time = $redirectData->getTimestamp();
        $this->assertEquals('{"customer_id":1}', $redirectData->getData());
        // phpcs:ignore Magento2.Security.InsecureFunction
        $this->assertEquals(md5("{\"customer_id\":1},{$time},fr,en"), $redirectData->getSignature());
    }

    public function testShouldGenerateEmptyDataIfDataSerializationFailed(): void
    {
        $this->dataSerializer->method('serialize')
            ->willThrowException(new \InvalidArgumentException('Failed to connect to cache server'));

        $redirectData = $this->model->generate($this->context);
        $time = time();
        $this->assertEqualsWithDelta($time, $redirectData->getTimestamp(), 1);
        $time = $redirectData->getTimestamp();
        $this->assertEquals('', $redirectData->getData());
        // phpcs:ignore Magento2.Security.InsecureFunction
        $this->assertEquals(md5(",{$time},fr,en"), $redirectData->getSignature());
    }
}
