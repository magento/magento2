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

class Services extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Magento_CurrencySymbol::system/currency/rate/services.phtml';

    /**
     * @var \Magento\Directory\Model\Currency\Import\Source\ServiceFactory
     */
    protected $_srcCurrencyFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Directory\Model\Currency\Import\Source\ServiceFactory $srcCurrencyFactory
     * @param array $data
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
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'import_services',
            $this->getLayout()->createBlock(
                'Magento\Framework\View\Element\Html\Select'
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
