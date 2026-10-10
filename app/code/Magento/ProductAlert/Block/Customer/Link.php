<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Block\Customer;

use Magento\Customer\Block\Account\SortLink;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\ProductAlert\Helper\Data as ProductAlertHelper;

/**
 * Customer account navigation link for product alerts.
 */
class Link extends SortLink
{
    /**
     * @param Context $context
     * @param DefaultPathInterface $defaultPath
     * @param ProductAlertHelper $productAlertHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        private ProductAlertHelper $productAlertHelper,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        $alertType = (string)$this->getData('alert_type');

        if ($alertType === 'price' && !$this->productAlertHelper->isPriceAlertAllowed()) {
            return '';
        }

        if ($alertType === 'stock' && !$this->productAlertHelper->isStockAlertAllowed()) {
            return '';
        }

        if (($alertType === '' || $alertType === 'all')
            && !$this->productAlertHelper->isPriceAlertAllowed()
            && !$this->productAlertHelper->isStockAlertAllowed()
        ) {
            return '';
        }

        return parent::_toHtml();
    }
}
