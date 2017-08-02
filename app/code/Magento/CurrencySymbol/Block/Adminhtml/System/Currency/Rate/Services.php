<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Manage currency import services block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CurrencySymbol\Block\Adminhtml\System\Currency\Rate;

/**
 * Class \Magento\CurrencySymbol\Block\Adminhtml\System\Currency\Rate\Services
 *
 * @since 2.0.0
 */
class Services extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'system/currency/rate/services.phtml';

    /**
     * @var \Magento\Directory\Model\Currency\Import\Source\ServiceFactory
     * @since 2.0.0
     */
    protected $_srcCurrencyFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Directory\Model\Currency\Import\Source\ServiceFactory $srcCurrencyFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Directory\Model\Currency\Import\Source\ServiceFactory $srcCurrencyFactory,
        array $data = []
    ) {
        $this->_srcCurrencyFactory = $srcCurrencyFactory;
        parent::__construct($context, $data);
    }

    /**
     * Create import services form select element
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'import_services',
            $this->getLayout()->createBlock(
                \Magento\Framework\View\Element\Html\Select::class
            )->setOptions(
                $this->_srcCurrencyFactory->create()->toOptionArray()
            )->setId(
                'rate_services'
            )->setClass(
                'admin__control-select'
            )->setName(
                'rate_services'
            )->setValue(
                $this->_backendSession->getCurrencyRateService(true)
            )->setTitle(
                __('Import Service')
            )
        );

        return parent::_prepareLayout();
    }
}
