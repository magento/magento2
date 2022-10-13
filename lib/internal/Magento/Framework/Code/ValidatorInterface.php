<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Code;

/**
 * Interface \Magento\Framework\Code\ValidatorInterface
 *
 * @api
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
