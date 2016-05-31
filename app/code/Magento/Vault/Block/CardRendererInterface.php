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
interface CardRendererInterface extends TokenRendererInterface
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
     * @return string
     */
    public function getIconUrl();

    /**
     * @return int
     */
    public function getIconHeight();

    /**
     * @return int
     */
    public function getIconWidth();

    /**
     * @return PaymentTokenInterface
     */
    public function getToken();
}
