<?php

namespace Liip\CustomerHierarchy\Block\Widget;

use \Magento\Customer\Block\Widget\AbstractWidget;

class AccountType extends AbstractWidget
{
    public function isRequired()
    {
        return true;
    }

    /**
     * @return array
     */
    public function getAccountTypeOptions()
    {
        return [
            [
                'value' => 'private',
                'label' => __('Private'),
            ],
            [
                'value' => 'corporate',
                'label' => __('Corporate'),
            ],
        ];
    }

    public function getAccountType()
    {
        return '';
    }
}
