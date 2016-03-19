<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * @param bool $noEmpty
     * @return array
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
