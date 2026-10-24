<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
namespace Magento\Usps\Model\Source;

/**
 * Freemethod source
 */
class Freemethod extends Method
{
    /**
     * @param \Magento\Usps\Model\Carrier $shippingUsps
     */
    public function __construct(\Magento\Usps\Model\Carrier $shippingUsps)
    {
        parent::__construct($shippingUsps);
        $this->code = $this->getUspsTypeMethodCode();
    }

    /**
     * Get dynamic code based on USPS type configuration
     *
     * @return string
     */
    private function getUspsTypeMethodCode(): string
    {
        $uspsType = $this->shippingUsps->getConfigData('usps_type');

        return match ($uspsType) {
            'USPS_REST' => 'rest_method',
            default => 'method',
        };
    }
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();

        array_unshift($options, ['value' => '', 'label' => __('None')]);
        return $options;
    }
}
