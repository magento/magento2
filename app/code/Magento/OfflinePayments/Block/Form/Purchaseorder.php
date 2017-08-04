<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Block\Form;

/**
 * Class \Magento\OfflinePayments\Block\Form\Purchaseorder
 *
 */
class Purchaseorder extends \Magento\Payment\Block\Form
{
    /**
     * Purchase order template
     *
     * @var string
     */
    protected $_template = 'Magento_OfflinePayments::form/purchaseorder.phtml';
}
