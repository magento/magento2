<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

use Magento\Framework\Encryption\Encryptor;
use Psr\Log\LoggerInterface;

/**
 * Store switcher redirect data collector
 */
class RedirectDataGenerator
{
    /**
     * @var RedirectDataPreprocessorInterface
     */
    private $preprocessor;
    /**
     * @var RedirectDataSerializerInterface
     */
    private $dataSerializer;
    /**
     * @var RedirectDataInterfaceFactory
     */
    private $dataFactory;
    /**
     * @var Encryptor
     */
    private $encryptor;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Encryptor $encryptor
     * @param RedirectDataPreprocessorInterface $preprocessor
     * @param RedirectDataSerializerInterface $dataSerializer
     * @param RedirectDataInterfaceFactory $dataFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Encryptor $encryptor,
        RedirectDataPreprocessorInterface $preprocessor,
        RedirectDataSerializerInterface $dataSerializer,
        RedirectDataInterfaceFactory $dataFactory,
        LoggerInterface $logger
    ) {
        $this->preprocessor = $preprocessor;
        $this->dataSerializer = $dataSerializer;
        $this->dataFactory = $dataFactory;
        $this->encryptor = $encryptor;
        $this->logger = $logger;
    }

    /**
     * Collect data to be redirected to the target store
     *
     * @param ContextInterface $context
     * @return RedirectDataInterface
     */
    public function generate(ContextInterface $context): RedirectDataInterface
    {
        $data = $this->preprocessor->process($context, []);
        try {
            $dataStr = $this->dataSerializer->serialize($data);
        } catch (\Throwable $exception) {
            $this->logger->error($exception);
            $dataStr = '';
        }
        $timestamp = time();
        $token = implode(
            ',',
            [
                $dataStr,
                $timestamp,
                $context->getFromStore()->getCode(),
                $context->getTargetStore()->getCode(),
            ]
        );
        $signature = $this->encryptor->hash($token, Encryptor::HASH_VERSION_SHA256);

        return $this->dataFactory->create(
            [
                'data' => $dataStr,
                'timestamp' => $timestamp,
                'signature' => $signature
            ]
        );
    }
}
