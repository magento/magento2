<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\System\Config\Source\Tax;

/**
 * Class \Magento\Tax\Model\System\Config\Source\Tax\Country
 *
 * @since 2.0.0
 */
class Country extends \Magento\Directory\Model\Config\Source\Country
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $_options;

    /**
     * @param bool $noEmpty
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray($noEmpty = false)
    {
        $options = parent::toOptionArray($noEmpty);

        if (!$noEmpty) {
            if ($options) {
                $options[0]['label'] = __('None');
            } else {
                $options = [['value' => '', 'label' => __('None')]];
            }
        }

        return $options;
    }
}
