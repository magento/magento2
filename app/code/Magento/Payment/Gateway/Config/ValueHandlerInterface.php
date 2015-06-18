<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Config;

interface ValueHandlerInterface
{
    /**
     * Retrieve method configured value
     *
     * @param string $field
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function handle($field, $storeId = null);
}
