<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Block\Adminhtml\Form\Field;

class Countries extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * Countries cache
     *
     * @var array
     */
    protected $countries;

    /**
     * @var \Magento\Braintree\Model\System\Config\Source\Country
     */
    protected $countrySource;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $countryCollectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Braintree\Model\System\Config\Source\Country $countrySource
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Braintree\Model\System\Config\Source\Country $countrySource,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->countrySource = $countrySource;
        $this->countryCollectionFactory = $countryCollectionFactory;
    }

    /**
     * Returns countries array
     * 
     * @return array
     */
    protected function _getCountries()
    {
        if (!$this->countries) {
            $restrictedCountries = $this->countrySource->getRestrictedCountries();
            $this->countries = $this->countryCollectionFactory->create()
                ->addFieldToFilter('country_id', ['nin' => $restrictedCountries])
                ->loadData()
                ->toOptionArray(false);
        }
        return $this->countries;
    }
    
    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getCountries() as $country) {
                if (isset($country['value']) && $country['value'] && isset($country['label']) && $country['label']) {
                    $this->addOption($country['value'], $country['label']);
                }
            }
        }
        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     * 
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
