<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\OfflinePayments\Model;

/**
 * Bank Transfer payment method model
 */
class Banktransfer extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_BANKTRANSFER_CODE = 'banktransfer';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_BANKTRANSFER_CODE;

    /**
     * Bank Transfer payment block paths
     *
     * @var string
     */
    protected $_formBlockType = 'Magento\OfflinePayments\Block\Form\Banktransfer';

    /**
     * Instructions block path
     *
     * @var string
     */
    protected $_infoBlockType = 'Magento\Payment\Block\Info\Instructions';

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        return trim($this->getConfigData('instructions'));
    }
}
