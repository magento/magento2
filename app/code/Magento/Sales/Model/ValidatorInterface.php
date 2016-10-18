<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Exception\DocumentValidationException;

/**
 * Interface ValidatorInterface
 */
interface ValidatorInterface
{
    /**
     * @param object $entity
     * @return \Magento\Framework\Phrase[]
     * @throws DocumentValidationException
     * @throws NoSuchEntityException
     */
    public function validate($entity);
}
