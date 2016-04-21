<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Model\MethodInterface;

/**
 * Class VaultPayment
 */
class VaultProvidersMap implements ArrayInterface
{
    const EMPTY_VALUE = null;

    const VALUE_CODE = 'vault_payment';

    /**
     * @var array
     */
    private $options = [];

    /**
     * Constructor
     *
     * @param MethodInterface[] $options
     */
    public function __construct(array $options = [])
    {
        $configuredMethods = array_map(
            function (MethodInterface $paymentMethod) {
                return [
                    'value' => $paymentMethod->getCode(),
                    'label' => __($paymentMethod->getCode())
                ];
            },
            $options
        );

        if (!empty($configuredMethods)) {
            $this->options = array_merge(
                [
                    [
                        'value' => self::EMPTY_VALUE,
                        'label' => __('Select vault provider')
                    ]
                ],
                $configuredMethods
            );
        }
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return $this->options;
    }
}
