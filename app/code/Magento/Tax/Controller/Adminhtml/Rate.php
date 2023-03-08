<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page as ResultPage;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Tax\Model\Calculation\Rate\Converter;

/**
 * Adminhtml tax rate controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Rate extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Tax::manage_tax';

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Converter
     */
    protected $_taxRateConverter;

    /**
     * @var TaxRateRepositoryInterface
     */
    protected $_taxRateRepository;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Converter $taxRateConverter
     * @param TaxRateRepositoryInterface $taxRateRepository
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Converter $taxRateConverter,
        TaxRateRepositoryInterface $taxRateRepository
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
                $result[$key] = $value !== null ? trim($value) : '';
            }
        }
        return $result;
    }

    /**
     * Initialize action
     *
     * @return ResultPage
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
