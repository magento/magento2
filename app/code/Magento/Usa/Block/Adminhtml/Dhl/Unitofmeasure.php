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
 * @category    Magento
 * @package     Magento_Usa
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Frontend model for DHL shipping methods for documentation
 *
 * @category   Magento
 * @package    Magento_Usa
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Usa\Block\Adminhtml\Dhl;

class Unitofmeasure extends \Magento\Backend\Block\System\Config\Form\Field
{

    /**
     * Usa data
     *
     * @var \Magento\Usa\Helper\Data
     */
    protected $_usaData = null;

    /**
     * @var \Magento\Usa\Model\Shipping\Carrier\Dhl\International
     */
    protected $_shippingDhl;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Usa\Model\Shipping\Carrier\Dhl\International $shippingDhl
     * @param \Magento\Usa\Helper\Data $usaData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Usa\Model\Shipping\Carrier\Dhl\International $shippingDhl,
        \Magento\Usa\Helper\Data $usaData,
        array $data = array()
    ) {
        $this->_shippingDhl = $shippingDhl;
        $this->_usaData = $usaData;
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

        $carrierModel = $this->_shippingDhl;

        $this->setInch($this->escapeJsQuote($carrierModel->getCode('unit_of_dimension_cut', 'I')));
        $this->setCm($this->escapeJsQuote($carrierModel->getCode('unit_of_dimension_cut', 'C')));

        $this->setHeight($this->escapeJsQuote($carrierModel->getCode('dimensions', 'height')));
        $this->setDepth($this->escapeJsQuote($carrierModel->getCode('dimensions', 'depth')));
        $this->setWidth($this->escapeJsQuote($carrierModel->getCode('dimensions', 'width')));

        $kgWeight = 70;

        $this->setDivideOrderWeightNoteKg(
            $this->escapeJsQuote(__('This allows breaking total order weight into smaller pieces if it exceeds %1 %2 to ensure accurate calculation of shipping charges.', $kgWeight, 'kg'))
        );

        $weight = round(
            $this->_usaData->convertMeasureWeight(
                $kgWeight, \Zend_Measure_Weight::KILOGRAM, \Zend_Measure_Weight::POUND), 3);

        $this->setDivideOrderWeightNoteLbp(
            $this->escapeJsQuote(__('This allows breaking total order weight into smaller pieces if it exceeds %1 %2 to ensure accurate calculation of shipping charges.', $weight, 'pounds'))
        );

        $this->setTemplate('dhl/unitofmeasure.phtml');
    }

    /**
     * Retrieve Element HTML fragment
     *
     * @param \Magento\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Data\Form\Element\AbstractElement $element)
    {
        return parent::_getElementHtml($element) . $this->_toHtml();
    }
}
