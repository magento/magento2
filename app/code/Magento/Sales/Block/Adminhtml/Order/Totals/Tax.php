<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Totals;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Math\Random;

/**
 * Adminhtml order tax totals block
 *
 * @api
 * @since 100.0.2
 */
class Tax extends \Magento\Tax\Block\Sales\Order\Tax
{
    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxHelper;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $_taxCalculation;

    /**
     * @var \Magento\Tax\Model\Sales\Order\TaxFactory
     */
    protected $_taxOrderFactory;

    /**
     * @var \Magento\Sales\Helper\Admin
     */
    protected $_salesAdminHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Tax\Model\Sales\Order\TaxFactory $taxOrderFactory
     * @param \Magento\Sales\Helper\Admin $salesAdminHelper
     * @param array $data
     * @param Random $randomHelper
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\Sales\Order\TaxFactory $taxOrderFactory,
        \Magento\Sales\Helper\Admin $salesAdminHelper,
        array $data = [],
        ?Random $randomHelper = null
    ) {
        $this->_taxHelper = $taxHelper;
        $this->_taxCalculation = $taxCalculation;
        $this->_taxOrderFactory = $taxOrderFactory;
        $this->_salesAdminHelper = $salesAdminHelper;
        $data['taxHelper'] = $this->_taxHelper;
        $data['randomHelper'] = $randomHelper ?? ObjectManager::getInstance()->get(Random::class);
        parent::__construct($context, $taxConfig, $data);
    }

    /**
     * Get full information about taxes applied to order
     *
     * @return array
     */
    public function getFullTaxInfo()
    {
        $source = $this->getSource();
        if (!$source instanceof \Magento\Sales\Model\Order\Invoice
            && !$source instanceof \Magento\Sales\Model\Order\Creditmemo
        ) {
            $source = $this->getOrder();
        }

        $taxClassAmount = [];
        if ($source == null) {
            return $taxClassAmount;
        }

        $taxClassAmount = $this->_taxHelper->getCalculatedTaxes($source);
        if (empty($taxClassAmount)) {
            $rates = $this->_taxOrderFactory->create()->getCollection()->loadByOrder($this->getOrder())->toArray();
            $taxClassAmount = $this->_taxCalculation->reproduceProcess($rates['items']);
        }

        return $taxClassAmount;
    }

    /**
     * Display tax amount
     *
     * @param string $amount
     * @param string $baseAmount
     * @return string
     */
    public function displayAmount($amount, $baseAmount)
    {
        return $this->_salesAdminHelper->displayPrices($this->getSource(), $baseAmount, $amount, false, '<br />');
    }

    /**
     * Get store object for process configuration settings
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }
}
