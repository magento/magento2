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
    protected $_template = 'system/currency/rate/services.phtml';

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
        array $data = array()
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
