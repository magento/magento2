<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

interface PaymentMethodInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_CODE = 'code';

    const KEY_TITLE = 'title';

    /**#@-*/

    /**
     * Get payment method code
     *
     * @return string
     */
    public function getCode();

    /**
     * Set payment method code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Get payment method title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set payment method title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title);
}
