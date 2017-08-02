<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Exception\DocumentValidationException;

/**
 * Interface ValidatorInterface
 * @since 2.2.0
 */
interface ValidatorInterface
{
    /**
     * @param object $entity
     * @return \Magento\Framework\Phrase[]
     * @throws DocumentValidationException
     * @throws NoSuchEntityException
     * @since 2.2.0
     */
    public function validate($entity);
}
