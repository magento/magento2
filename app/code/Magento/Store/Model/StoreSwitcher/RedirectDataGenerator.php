<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

use Magento\Framework\Encryption\Encryptor;

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
    private $serializer;
    /**
     * @var RedirectDataInterfaceFactory
     */
    private $dataFactory;
    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @param Encryptor $encryptor
     * @param RedirectDataPreprocessorInterface $preprocessor
     * @param RedirectDataSerializerInterface $serializer
     * @param RedirectDataInterfaceFactory $dataFactory
     */
    public function __construct(
        Encryptor $encryptor,
        RedirectDataPreprocessorInterface $preprocessor,
        RedirectDataSerializerInterface $serializer,
        RedirectDataInterfaceFactory $dataFactory
    ) {
        $this->preprocessor = $preprocessor;
        $this->serializer = $serializer;
        $this->dataFactory = $dataFactory;
        $this->encryptor = $encryptor;
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
        $dataStr = $this->serializer->serialize($data);
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
