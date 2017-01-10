<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Controller\Adminhtml;

use Magento\Framework\Controller\ResultFactory;

/**
 * Adminhtml tax rate controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Tax\Model\Calculation\Rate\Converter
     */
    protected $_taxRateConverter;

    /**
     * @var \Magento\Tax\Api\TaxRateRepositoryInterface
     */
    protected $_taxRateRepository;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Tax\Model\Calculation\Rate\Converter $taxRateConverter
     * @param \Magento\Tax\Api\TaxRateRepositoryInterface $taxRateRepository
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
