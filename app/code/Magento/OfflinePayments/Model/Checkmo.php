<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\OfflinePayments\Model;

class Checkmo extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * @var string
     */
    protected $_code = 'checkmo';

    /**
     * @var string
     */
    protected $_formBlockType = 'Magento\OfflinePayments\Block\Form\Checkmo';

    /**
     * @var string
     */
    protected $_infoBlockType = 'Magento\OfflinePayments\Block\Info\Checkmo';

    /**
     * Assign data to info model instance
     *
     * @param mixed $data
     * @return $this
     */
    public function assignData($data)
    {
        $details = [];
        if ($this->getPayableTo()) {
            $details['payable_to'] = $this->getPayableTo();
        }
        if ($this->getMailingAddress()) {
            $details['mailing_address'] = $this->getMailingAddress();
        }
        if (!empty($details)) {
            $this->getInfoInstance()->setAdditionalData(serialize($details));
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPayableTo()
    {
        return $this->getConfigData('payable_to');
    }

    /**
     * @return string
     */
    public function getMailingAddress()
    {
        return $this->getConfigData('mailing_address');
    }
}
