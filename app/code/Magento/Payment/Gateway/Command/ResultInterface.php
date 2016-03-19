<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Command;

interface ResultInterface
{
    /**
     * Returns result interpretation
     *
     * @return mixed
     */
    public function get();
}
