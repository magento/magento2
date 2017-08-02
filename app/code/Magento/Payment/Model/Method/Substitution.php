<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Model\Method;

/**
 * Substitution payment method for non-existing payments
 *
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 * @since 2.0.0
 */
class Substitution extends AbstractMethod
{
    /**
     * Method code
     */
    const CODE = 'substitution';

    /**
     * Key of title in instance additional information
     */
    const INFO_KEY_TITLE = 'method_title';

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_code = self::CODE;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_infoBlockType = \Magento\Payment\Block\Info\Substitution::class;

    /**
     * Retrieve payment method title
     *
     * @return string
     * @since 2.0.0
     */
    public function getTitle()
    {
        return $this->getInfoInstance()->getAdditionalInformation(self::INFO_KEY_TITLE);
    }
}
