<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Usps\Model\Source;

use Magento\Shipping\Model\Carrier\Source\GenericInterface;
use Magento\Usps\Model\Carrier;

/**
 * Generic source
 */
class Generic implements GenericInterface
{
    /**
     * Carrier code
     *
     * @var string
     */
    protected $code = '';

    /**
     * @param Carrier $shippingUsps
     */
    public function __construct(
        protected readonly Carrier $shippingUsps
    ) {
    }

    /**
     * Returns array to be used in multiselect on back-end
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $codes = $this->shippingUsps->getCode($this->code);
        if ($codes) {
            foreach ($codes as $code => $title) {
                $options[] = ['value' => $code, 'label' => __($title)];
            }
        }
        return $options;
    }
}
