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

/**
 * Tax rule controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Controller\Adminhtml;

use Magento\Backend\App\Action;

class Rule extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /** @var \Magento\Tax\Service\V1\TaxRuleServiceInterface */
    protected $ruleService;

    /** @var \Magento\Tax\Service\V1\Data\TaxRuleBuilder */
    protected $ruleBuilder;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Tax\Service\V1\TaxRuleServiceInterface $ruleService
     * @param \Magento\Tax\Service\V1\Data\TaxRuleBuilder $ruleBuilder
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Tax\Service\V1\TaxRuleServiceInterface $ruleService,
        \Magento\Tax\Service\V1\Data\TaxRuleBuilder $ruleBuilder
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->ruleService = $ruleService;
        $this->ruleBuilder = $ruleBuilder;
        parent::__construct($context);
    }

    /**
     * Initialize action
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_Tax::sales_tax_rules'
        )->_addBreadcrumb(
            __('Tax'),
            __('Tax')
        )->_addBreadcrumb(
            __('Tax Rules'),
            __('Tax Rules')
        );
        return $this;
    }

    /**
     * Check if sales rule is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Tax::manage_tax');
    }

    /**
     * Initialize tax rule service object with form data.
     *
     * @param array $postData
     * @return \Magento\Tax\Service\V1\Data\TaxRule
     */
    protected function populateTaxRule($postData)
    {
        if (isset($postData['tax_calculation_rule_id'])) {
            $this->ruleBuilder->setId($postData['tax_calculation_rule_id']);
        }
        if (isset($postData['code'])) {
            $this->ruleBuilder->setCode($postData['code']);
        }
        if (isset($postData['tax_rate'])) {
            $this->ruleBuilder->setTaxRateIds($postData['tax_rate']);
        }
        if (isset($postData['tax_customer_class'])) {
            $this->ruleBuilder->setCustomerTaxClassIds($postData['tax_customer_class']);
        }
        if (isset($postData['tax_product_class'])) {
            $this->ruleBuilder->setProductTaxClassIds($postData['tax_product_class']);
        }
        if (isset($postData['priority'])) {
            $this->ruleBuilder->setPriority($postData['priority']);
        }
        if (isset($postData['calculate_subtotal'])) {
            $this->ruleBuilder->setCalculateSubtotal($postData['calculate_subtotal']);
        }
        if (isset($postData['position'])) {
            $this->ruleBuilder->setSortOrder($postData['position']);
        }
        return $this->ruleBuilder->create();
    }
}
