<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Block\Adminhtml\Order\Totals;

/**
 * Adminhtml order tax totals block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Tax extends \Magento\Tax\Block\Sales\Order\Tax
{
    /**
     * Tax helper
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxHelper;

    /**
     * Tax calculation
     *
     * @var \Magento\Tax\Model\Calculation
     */
    protected $_taxCalculation;

    /**
     * Tax factory
     *
     * @var \Magento\Tax\Model\Sales\Order\TaxFactory
     */
    protected $_taxOrderFactory;

    /**
     * Sales admin helper
     *
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
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\Sales\Order\TaxFactory $taxOrderFactory,
        \Magento\Sales\Helper\Admin $salesAdminHelper,
        array $data = array()
    ) {
        $this->_taxHelper = $taxHelper;
        $this->_taxCalculation = $taxCalculation;
        $this->_taxOrderFactory = $taxOrderFactory;
        $this->_salesAdminHelper = $salesAdminHelper;
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
        if (empty($source)) {
            return $taxClassAmount;
        }

        $taxClassAmount = $this->_taxHelper->getCalculatedTaxes($source);
        if (empty($taxClassAmount)) {
            $rates = $this->_taxOrderFactory->create()->getCollection()->loadByOrder($source)->toArray();
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
