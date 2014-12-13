<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Checkout\Service\V1\Data;

/**
 * @codeCoverageIgnore
 */
class PaymentMethod extends \Magento\Framework\Api\AbstractExtensibleObject
{
    const CODE = 'code';

    const TITLE = 'title';

    /**
     * Get payment method code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->_get(self::CODE);
    }

    /**
     * Get payment method title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_get(self::TITLE);
    }
}
