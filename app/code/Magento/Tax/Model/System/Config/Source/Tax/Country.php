<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
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
