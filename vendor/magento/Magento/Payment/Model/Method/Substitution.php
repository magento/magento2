<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
