<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Exception\DocumentValidationException;
use Magento\Sales\Model\ValidatorInterface;
use Magento\Sales\Model\ValidatorResultInterface;

/**
 * Interface CreditmemoValidatorInterface
 */
interface CreditmemoValidatorInterface
{
    /**
     * @param CreditmemoInterface $entity
     * @param ValidatorInterface[] $validators
     * @return ValidatorResultInterface
     * @throws DocumentValidationException
     */
    public function validate(CreditmemoInterface $entity, array $validators);
}
