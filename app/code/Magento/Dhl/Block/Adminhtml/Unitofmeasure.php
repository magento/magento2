<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Dhl\Block\Adminhtml;

use Magento\Dhl\Model;
use Magento\Shipping\Helper;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Frontend model for DHL shipping methods for documentation
 */
class Unitofmeasure extends Field
{
    /**
     * Carrier helper
     *
     * @var Helper\Carrier
     */
    protected $carrierHelper;

    /**
     * @var Model\Carrier
     */
    protected $carrierDhl;

    /**
     * @param Context $context
     * @param Model\Carrier $carrierDhl
     * @param Helper\Carrier $carrierHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Model\Carrier $carrierDhl,
        Helper\Carrier $carrierHelper,
        array $data = []
    ) {
        $this->carrierDhl = $carrierDhl;
        $this->carrierHelper = $carrierHelper;
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

        $this->setInch($this->carrierDhl->getCode('unit_of_dimension_cut', 'I'));
        $this->setCm($this->carrierDhl->getCode('unit_of_dimension_cut', 'C'));

        $this->setHeight($this->carrierDhl->getCode('dimensions', 'height'));
        $this->setDepth($this->carrierDhl->getCode('dimensions', 'depth'));
        $this->setWidth($this->carrierDhl->getCode('dimensions', 'width'));

        $kgWeight = 70;

        $this->setDivideOrderWeightNoteKg(
            __(
                'Select this to allow DHL to optimize shipping charges by splitting the order if it exceeds %1 %2.',
                $kgWeight,
                'kg'
            )
        );

        $weight = round(
            $this->carrierHelper->convertMeasureWeight(
                $kgWeight,
                \Zend_Measure_Weight::KILOGRAM,
                \Zend_Measure_Weight::POUND
            ),
            3
        );

        $this->setDivideOrderWeightNoteLbp(
            __(
                'Select this to allow DHL to optimize shipping charges by splitting the order if it exceeds %1 %2.',
                $weight,
                'pounds'
            )
        );

        $this->setTemplate('unitofmeasure.phtml');
    }

    /**
     * Retrieve Element HTML fragment
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return parent::_getElementHtml($element) . $this->_toHtml();
    }
}
