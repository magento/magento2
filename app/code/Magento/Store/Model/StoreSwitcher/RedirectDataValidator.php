<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

use Magento\Framework\Encryption\Encryptor;

/**
 * Store switcher redirect data validator
 */
class RedirectDataValidator
{
    private const TIMEOUT = 5;
    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @param Encryptor $encryptor
     */
    public function __construct(
        Encryptor $encryptor
    ) {
        $this->encryptor = $encryptor;
    }

    /**
     * Validate data redirected from origin store
     *
     * @param ContextInterface $context
     * @param RedirectDataInterface $redirectData
     * @return bool
     */
    public function validate(ContextInterface $context, RedirectDataInterface $redirectData)
    {
        $timeStamp = $redirectData->getTimestamp();
        $signature = $redirectData->getSignature();
        $value = implode(
            ',',
            [
                $redirectData->getData(),
                $timeStamp,
                $context->getFromStore()->getCode(),
                $context->getTargetStore()->getCode()
            ]
        );
        return time() - $timeStamp <= self::TIMEOUT
            && $this->encryptor->validateHash($value, $signature);
    }
}
