<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Controller\Adminhtml;

use Magento\Framework\Controller\ResultFactory;

/**
 * Adminhtml tax rate controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
abstract class Rate extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Tax::manage_tax';

    /**
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Tax\Model\Calculation\Rate\Converter
     * @since 2.0.0
     */
    protected $_taxRateConverter;

    /**
     * @var \Magento\Tax\Api\TaxRateRepositoryInterface
     * @since 2.0.0
     */
    protected $_taxRateRepository;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Tax\Model\Calculation\Rate\Converter $taxRateConverter
     * @param \Magento\Tax\Api\TaxRateRepositoryInterface $taxRateRepository
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Tax\Model\Calculation\Rate\Converter $taxRateConverter,
        \Magento\Tax\Api\TaxRateRepositoryInterface $taxRateRepository
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_taxRateConverter = $taxRateConverter;
        $this->_taxRateRepository = $taxRateRepository;
        parent::__construct($context);
    }

    /**
     * Validate/Filter Rate Data
     *
     * @param array $rateData
     * @return array
     * @since 2.0.0
     */
    protected function _processRateData($rateData)
    {
        $result = [];
        foreach ($rateData as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->_processRateData($value);
            } else {
                $result[$key] = trim($value);
            }
        }
        return $result;
    }

    /**
     * Initialize action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     * @since 2.0.0
     */
    protected function initResultPage()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_Tax::sales_tax_rates')
            ->addBreadcrumb(__('Sales'), __('Sales'))
            ->addBreadcrumb(__('Tax'), __('Tax'));
        return $resultPage;
    }
}
