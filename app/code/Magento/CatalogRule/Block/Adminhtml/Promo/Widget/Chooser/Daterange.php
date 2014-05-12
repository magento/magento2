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
 * Date range promo widget chooser
 * Currently works without localized format
 */
namespace Magento\CatalogRule\Block\Adminhtml\Promo\Widget\Chooser;

class Daterange extends \Magento\Backend\Block\AbstractBlock
{
    /**
     * HTML ID of the element that will obtain the joined chosen values
     *
     * @var string
     */
    protected $_targetElementId = '';

    /**
     * From/To values to be rendered
     *
     * @var array
     */
    protected $_rangeValues = array('from' => '', 'to' => '');

    /**
     * Range string delimiter for from/to dates
     *
     * @var string
     */
    protected $_rangeDelimiter = '...';

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $_formFactory;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Math\Random $mathRandom,
        array $data = array()
    ) {
        $this->_formFactory = $formFactory;
        $this->mathRandom = $mathRandom;
        parent::__construct($context, $data);
    }

    /**
     * Render the chooser HTML
     * Target element should be set.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (empty($this->_targetElementId)) {
            return '';
        }

        $idSuffix = $this->mathRandom->getUniqueHash();
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $dateFields = array('from' => __('From'), 'to' => __('To'));
        foreach ($dateFields as $key => $label) {
            $form->addField(
                "{$key}_{$idSuffix}",
                'date',
                array(
                    // hardcoded because hardcoded values delimiter
                    'format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                    'label' => $label,
                    'image' => $this->getViewFileUrl('images/grid-cal.gif'),
                    // won't work through Event.observe()
                    'onchange' => "dateTimeChoose_{$idSuffix}()",
                    'value' => $this->_rangeValues[$key]
                )
            );
        }
        return $form->toHtml() .
            "<script type=\"text/javascript\">\n            dateTimeChoose_{$idSuffix} = function() {\n                \$('{$this->_targetElementId}').value = " .
            "\$('from_{$idSuffix}').value + '{$this->_rangeDelimiter}' + \$('to_{$idSuffix}').value;\n            };\n            </script>";
    }

    /**
     * Target element ID setter
     *
     * @param string $value
     * @return $this
     */
    public function setTargetElementId($value)
    {
        $this->_targetElementId = trim($value);
        return $this;
    }

    /**
     * Range values setter
     *
     * @param string $from
     * @param string $to
     * @return $this
     */
    public function setRangeValues($from, $to)
    {
        $this->_rangeValues = array('from' => $from, 'to' => $to);
        return $this;
    }

    /**
     * Range values setter, string implementation.
     * Automatically attempts to split the string by delimiter
     *
     * @param string $delimitedString
     * @return $this
     */
    public function setRangeValue($delimitedString)
    {
        $split = explode($this->_rangeDelimiter, $delimitedString, 2);
        $from = $split[0];
        $to = '';
        if (isset($split[1])) {
            $to = $split[1];
        }
        return $this->setRangeValues($from, $to);
    }

    /**
     * Range delimiter setter
     *
     * @param string $value
     * @return $this
     */
    public function setRangeDelimiter($value)
    {
        $this->_rangeDelimiter = (string)$value;
        return $this;
    }
}
