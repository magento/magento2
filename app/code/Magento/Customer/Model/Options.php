<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Config\Model\Config\Source\Nooptreq as NooptreqSource;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Framework\Escaper;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Customer Options.
 */
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
     * @param null|string|bool|int|StoreInterface $store
     * @return array|bool
     */
    public function getNamePrefixOptions($store = null)
    {
        return $this->prepareNamePrefixSuffixOptions(
            $this->addressHelper->getConfig('prefix_options', $store),
            $this->addressHelper->getConfig('prefix_show', $store) == NooptreqSource::VALUE_OPTIONAL
        );
    }

    /**
     * Retrieve name suffix dropdown options
     *
     * @param null|string|bool|int|StoreInterface $store
     * @return array|bool
     */
    public function getNameSuffixOptions($store = null)
    {
        return $this->prepareNamePrefixSuffixOptions(
            $this->addressHelper->getConfig('suffix_options', $store),
            $this->addressHelper->getConfig('suffix_show', $store) == NooptreqSource::VALUE_OPTIONAL
        );
    }

    /**
     * Unserialize and clear name prefix or suffix options.
     *
     * @param string $options
     * @param bool $isOptional
     * @return array|bool
     *
     * @deprecated 101.0.4
     * @see prepareNamePrefixSuffixOptions()
     */
    protected function _prepareNamePrefixSuffixOptions($options, $isOptional = false)
    {
        return $this->prepareNamePrefixSuffixOptions($options, $isOptional);
    }

    /**
     * Unserialize and clear name prefix or suffix options
     *
     * If field is optional, add an empty first option.
     *
     * @param string $options
     * @param bool $isOptional
     * @return array|bool
     */
    private function prepareNamePrefixSuffixOptions($options, $isOptional = false)
    {
        $options = trim($options);
        if (empty($options)) {
            return false;
        }

        $result = [];
        $options = explode(';', $options);
        foreach ($options as $value) {
            $result[] = $this->escaper->escapeHtml(trim($value)) ?: ' ';
        }

        if ($isOptional && trim(current($options))) {
            $result = array_merge([' '], $result);
        }

        return $result;
    }
}
