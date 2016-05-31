<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Method;

use Magento\Payment\Model\MethodInterface;

/**
 * Interface TransparentInterface need to be implemented by Payment Method service
 * which supports transparent redirect feature
 * @package Magento\Payment\Model\Method
 */
interface TransparentInterface extends MethodInterface
{
    /**
     * Returns payment method configured config
     *
     * @return \Magento\Payment\Gateway\ConfigInterface
     */
    public function getConfigInterface();
}
