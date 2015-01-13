<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Dhl\Block\Adminhtml;

/**
 * Frontend model for DHL shipping methods for documentation
 */
class Unitofmeasure extends \Magento\Backend\Block\System\Config\Form\Field
{
    /**
     * Carrier helper
     *
     * @var \Magento\Shipping\Helper\Carrier
     */
    protected $_carrierHelper;

    /**
     * @var \Magento\Dhl\Model\Carrier
     */
    protected $carrierDhl;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Dhl\Model\Carrier $carrierDhl
     * @param \Magento\Shipping\Helper\Carrier $carrierHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Dhl\Model\Carrier $carrierDhl,
        \Magento\Shipping\Helper\Carrier $carrierHelper,
        array $data = []
    ) {
        $this->carrierDhl = $carrierDhl;
        $this->_carrierHelper = $carrierHelper;
        parent::__construct($context, $data);
    }

    /**
     * Define params and variables
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $carrierModel = $this->carrierDhl;

        $this->setInch($this->escapeJsQuote($carrierModel->getCode('unit_of_dimension_cut', 'I')));
        $this->setCm($this->escapeJsQuote($carrierModel->getCode('unit_of_dimension_cut', 'C')));

        $this->setHeight($this->escapeJsQuote($carrierModel->getCode('dimensions', 'height')));
        $this->setDepth($this->escapeJsQuote($carrierModel->getCode('dimensions', 'depth')));
        $this->setWidth($this->escapeJsQuote($carrierModel->getCode('dimensions', 'width')));

        $kgWeight = 70;

        $this->setDivideOrderWeightNoteKg(
            $this->escapeJsQuote(
                __(
                    'This allows breaking total order weight into smaller pieces if it exceeds %1 %2 to ensure accurate calculation of shipping charges.',
                    $kgWeight,
                    'kg'
                )
            )
        );

        $weight = round(
            $this->_carrierHelper->convertMeasureWeight(
                $kgWeight,
                \Zend_Measure_Weight::KILOGRAM,
                \Zend_Measure_Weight::POUND
            ),
            3
        );

        $this->setDivideOrderWeightNoteLbp(
            $this->escapeJsQuote(
                __(
                    'This allows breaking total order weight into smaller pieces if it exceeds %1 %2 to ensure accurate calculation of shipping charges.',
                    $weight,
                    'pounds'
                )
            )
        );

        $this->setTemplate('unitofmeasure.phtml');
    }

    /**
     * Retrieve Element HTML fragment
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return parent::_getElementHtml($element) . $this->_toHtml();
    }
}
