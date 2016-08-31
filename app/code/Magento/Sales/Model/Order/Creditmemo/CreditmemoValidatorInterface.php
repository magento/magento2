<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Exception\DocumentValidationException;
use Magento\Sales\Model\ValidatorInterface;

/**
 * Interface CreditmemoValidatorInterface
 */
interface CreditmemoValidatorInterface
{
    /**
     * @param CreditmemoInterface $entity
     * @param ValidatorInterface[] $validators
     * @return string[]
     * @throws DocumentValidationException
     */
    public function validate(CreditmemoInterface $entity, array $validators);
}
