<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Model\Method;

/**
 * Substitution payment method for non-existing payments
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
     */
    protected $_code = self::CODE;

    /**
     * @var string
     */
    protected $_infoBlockType = 'Magento\Payment\Block\Info\Substitution';

    /**
     * Retrieve payment method title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getInfoInstance()->getAdditionalInformation(self::INFO_KEY_TITLE);
    }
}
