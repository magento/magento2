<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleAuthorizenetAcceptjs\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

/**
 * Force validation of the transaction hash
 */
class TransactionHashValidator extends AbstractValidator
{
    /**
     * Skip validation of transaction hash in mock response
     *
     * @param array $validationSubject
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate(array $validationSubject): ResultInterface
    {
        return $this->createResult(true);
    }
}
