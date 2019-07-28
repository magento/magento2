<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code;

/**
 * Interface \Magento\Framework\Code\ValidatorInterface
 *
 */
interface ValidatorInterface
{
    /**
     * Validate class
     *
     * @param string $className
     * @return bool
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function validate($className);
}
