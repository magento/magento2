<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Framework\Escaper;

class Options
{
    /**
     * Customer address
     *
     * @var AddressHelper
     */
    protected $addressHelper;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @param AddressHelper $addressHelper
     * @param Escaper $escaper
     */
    public function __construct(
        AddressHelper $addressHelper,
        Escaper $escaper
    ) {
        $this->addressHelper = $addressHelper;
        $this->escaper = $escaper;
    }

    /**
     * Retrieve name prefix dropdown options
     *
     * @param null $store
     * @return array|bool
     */
    public function getNamePrefixOptions($store = null)
    {
        return $this->_prepareNamePrefixSuffixOptions($this->addressHelper->getConfig('prefix_options', $store));
    }

    /**
     * Retrieve name suffix dropdown options
     *
     * @param null $store
     * @return array|bool
     */
    public function getNameSuffixOptions($store = null)
    {
        return $this->_prepareNamePrefixSuffixOptions($this->addressHelper->getConfig('suffix_options', $store));
    }

    /**
     * Unserialize and clear name prefix or suffix options
     *
     * @param string $options
     * @return array|bool
     */
    protected function _prepareNamePrefixSuffixOptions($options)
    {
        $options = trim($options);
        if (empty($options)) {
            return false;
        }
        $result = [];
        $options = explode(';', $options);
        foreach ($options as $value) {
            $value = $this->escaper->escapeHtml(trim($value));
            $result[$value] = $value;
        }
        return $result;
    }
}
