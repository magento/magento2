<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\Listing\Column\Online\Type;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 * @since 2.0.0
 */
class Options implements OptionSourceInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $options;

    /**
     * Get options
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [
                [
                    'value' => \Magento\Customer\Model\Visitor::VISITOR_TYPE_VISITOR,
                    'label' => __('Visitor'),
                ],
                [
                    'value' => \Magento\Customer\Model\Visitor::VISITOR_TYPE_CUSTOMER,
                    'label' => __('Customer'),
                ]
            ];
        }
        return $this->options;
    }
}
