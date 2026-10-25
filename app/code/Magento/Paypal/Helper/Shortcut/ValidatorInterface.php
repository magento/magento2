<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Paypal\Helper\Shortcut;

/**
 * Interface \Magento\Paypal\Helper\Shortcut\ValidatorInterface
 *
 * @api
 */
interface ValidatorInterface
{
    /**
     * Validates shortcut
     *
     * @param string $code
     * @param bool $isInCatalog
     * @return bool
     */
    public function validate($code, $isInCatalog);
}
