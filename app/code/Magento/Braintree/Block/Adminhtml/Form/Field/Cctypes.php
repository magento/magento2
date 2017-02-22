<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Block\Adminhtml\Form\Field;

class Cctypes extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * All possible credit card types
     *
     * @var array
     */
    protected $ccTypes = [];

    /**
     * @var \Magento\Braintree\Model\Source\CcType
     */
    protected $ccTypeSource;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Braintree\Model\Source\CcType $ccTypeSource
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Braintree\Model\Source\CcType $ccTypeSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->ccTypeSource = $ccTypeSource;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getCcTypes() as $country) {
                if (isset($country['value']) && $country['value'] && isset($country['label']) && $country['label']) {
                    $this->addOption($country['value'], $country['label']);
                }
            }
        }
        $this->setClass('cc-type-select');
        $this->setExtraParams('multiple="multiple"');
        return parent::_toHtml();
    }

    /**
     * All possible credit card types
     *
     * @return array
     */
    protected function _getCcTypes()
    {
        if (!$this->ccTypes) {
            $this->ccTypes = $this->ccTypeSource->toOptionArray();
        }
        return $this->ccTypes;
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value . '[]');
    }
}
