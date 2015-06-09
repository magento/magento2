<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferInterface;

interface TransferBuilderInterface
{
    /**
     * Builds gateway transfer object
     *
     * @param array $requestENV
     * @return TransferInterface
     */
    public function build(array $requestENV);
}
