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
        return $this->_getAttribute('type')->getOptions();
    }

    public function getAccountType()
    {
        return '';
    }
}
