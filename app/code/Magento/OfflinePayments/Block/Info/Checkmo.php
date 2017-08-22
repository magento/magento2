<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Block\Info;

/**
 * Class \Magento\OfflinePayments\Block\Info\Checkmo
 *
 */
class Checkmo extends \Magento\Payment\Block\Info
{
    /**
     * @var string
     */
    protected $_payableTo;

    /**
     * @var string
     */
    protected $_mailingAddress;

    /**
     * @var string
     */
    protected $_template = 'Magento_OfflinePayments::info/checkmo.phtml';

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getPayableTo()
    {
        if ($this->_payableTo === null) {
            $this->_convertAdditionalData();
        }
        return $this->_payableTo;
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getMailingAddress()
    {
        if ($this->_mailingAddress === null) {
            $this->_convertAdditionalData();
        }
        return $this->_mailingAddress;
    }

    /**
     * @deprecated 100.1.1
     * @return $this
     */
    protected function _convertAdditionalData()
    {
        $this->_payableTo = $this->getInfo()->getAdditionalInformation('payable_to');
        $this->_mailingAddress = $this->getInfo()->getAdditionalInformation('mailing_address');
        return $this;
    }

    /**
     * @return string
     */
    public function toPdf()
    {
        $this->setTemplate('Magento_OfflinePayments::info/pdf/checkmo.phtml');
        return $this->toHtml();
    }
}
