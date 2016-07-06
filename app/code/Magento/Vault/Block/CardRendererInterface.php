<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block;

use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Interface CardRendererInterface
 * @api
 */
interface CardRendererInterface extends TokenRendererInterface, IconRendererInterface
{
    /**
     * @return string
     */
    public function getNumberLast4Digits();

    /**
     * @return string
     */
    public function getExpDate();

    /**
     * @return PaymentTokenInterface
     */
    public function getToken();
}
