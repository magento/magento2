<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Block\Info;

/**
 * Class \Magento\OfflinePayments\Block\Info\Purchaseorder
 *
 * @since 2.0.0
 */
class Purchaseorder extends \Magento\Payment\Block\Info
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_OfflinePayments::info/purchaseorder.phtml';

    /**
     * @return string
     * @since 2.0.0
     */
    public function toPdf()
    {
        $this->setTemplate('Magento_OfflinePayments::info/pdf/purchaseorder.phtml');
        return $this->toHtml();
    }
}
