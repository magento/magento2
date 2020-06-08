<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source;

/**
 * Get disable funding options
 */
class DisableFundingOptions
{

    /**
     * @var array
     */
    private $disallowedFundingOptions;

    /**
     * DisableFundingOptions constructor.
     * @param array $disallowedFundingOptions
     */
    public function __construct($disallowedFundingOptions = [])
    {
        $this->disallowedFundingOptions = $disallowedFundingOptions;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return array_map(
            function ($key, $value) {
                return [
                    'value' => $key,
                    'label' => __($value)
                ];
            },
            array_keys($this->disallowedFundingOptions),
            $this->disallowedFundingOptions
        );
    }
}
