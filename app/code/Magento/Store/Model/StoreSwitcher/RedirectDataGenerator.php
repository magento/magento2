<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

use Magento\Framework\Encryption\Encryptor;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Store switcher redirect data collector
 */
class RedirectDataGenerator
{
    /**
     * @param Encryptor $encryptor
     * @param RedirectDataPreprocessorInterface $preprocessor
     * @param RedirectDataSerializerInterface $dataSerializer
     * @param RedirectDataInterfaceFactory $dataFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Encryptor $encryptor,
        private readonly RedirectDataPreprocessorInterface $preprocessor,
        private readonly RedirectDataSerializerInterface $dataSerializer,
        private readonly RedirectDataInterfaceFactory $dataFactory,
        private readonly LoggerInterface $logger
    ) {
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
        } catch (Throwable $exception) {
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
