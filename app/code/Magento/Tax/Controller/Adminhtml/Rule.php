<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tax rule controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

abstract class Rule extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Tax::manage_tax';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /** @var \Magento\Tax\Api\TaxRuleRepositoryInterface */
    protected $ruleService;

    /** @var \Magento\Tax\Api\Data\TaxRuleInterfaceFactory */
    protected $taxRuleDataObjectFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Tax\Api\TaxRuleRepositoryInterface $ruleService
     * @param \Magento\Tax\Api\Data\TaxRuleInterfaceFactory $taxRuleDataObjectFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Tax\Api\TaxRuleRepositoryInterface $ruleService,
        \Magento\Tax\Api\Data\TaxRuleInterfaceFactory $taxRuleDataObjectFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->ruleService = $ruleService;
        $this->taxRuleDataObjectFactory = $taxRuleDataObjectFactory;
        parent::__construct($context);
    }

    /**
     * Initialize action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initResultPage()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_Tax::sales_tax_rules')
            ->addBreadcrumb(__('Tax'), __('Tax'))
            ->addBreadcrumb(__('Tax Rules'), __('Tax Rules'));
        return $resultPage;
    }

    /**
     * Initialize tax rule service object with form data.
     *
     * @param array $postData
     * @return \Magento\Tax\Api\Data\TaxRuleInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function populateTaxRule($postData)
    {
        $taxRule = $this->taxRuleDataObjectFactory->create();
        if (isset($postData['tax_calculation_rule_id'])) {
            $taxRule->setId($postData['tax_calculation_rule_id']);
        }
        if (isset($postData['code'])) {
            $taxRule->setCode($postData['code']);
        }
        if (isset($postData['tax_rate'])) {
            $taxRule->setTaxRateIds($postData['tax_rate']);
        }
        if (isset($postData['tax_customer_class'])) {
            $taxRule->setCustomerTaxClassIds($postData['tax_customer_class']);
        }
        if (isset($postData['tax_product_class'])) {
            $taxRule->setProductTaxClassIds($postData['tax_product_class']);
        }
        if (isset($postData['priority'])) {
            $taxRule->setPriority($postData['priority']);
        }
        if (isset($postData['calculate_subtotal'])) {
            $taxRule->setCalculateSubtotal($postData['calculate_subtotal']);
        }
        if (isset($postData['position'])) {
            $taxRule->setPosition($postData['position']);
        }
        return $taxRule;
    }
}
