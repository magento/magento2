<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\System\Config\Source\Tax;

class Country extends \Magento\Directory\Model\Config\Source\Country
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * @inheritdoc
     */
    public function toOptionArray($noEmpty = false, $foregroundCountries = '')
    {
        $options = parent::toOptionArray($noEmpty, $foregroundCountries);

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
