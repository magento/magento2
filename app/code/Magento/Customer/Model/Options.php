<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Framework\Escaper;

/**
 * Class \Magento\Customer\Model\Options
 *
 * @since 2.0.0
 */
class Options
{
    /**
     * Customer address
     *
     * @var AddressHelper
     * @since 2.0.0
     */
    protected $addressHelper;

    /**
     * @var Escaper
     * @since 2.0.0
     */
    protected $escaper;

    /**
     * @param AddressHelper $addressHelper
     * @param Escaper $escaper
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
