<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class VaultPayment
 */
class VaultPayment implements ArrayInterface
{
    const EMPTY_VALUE = 'empty_value';

    const VALUE_CODE = 'vault_payment';

    /**
     * @var array
     */
    private $options;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge(
            $options,
            [
                'value' => self::EMPTY_VALUE,
                'label' => __('Select a payment solution')
            ]
        );
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
