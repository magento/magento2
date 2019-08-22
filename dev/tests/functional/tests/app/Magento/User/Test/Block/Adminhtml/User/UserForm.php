<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Test\Block\Adminhtml\User;

use Magento\Backend\Test\Block\Widget\FormTabs;

/**
 * User edit form page
 */
class UserForm extends FormTabs
{
    /**
     * Interface Locale drop-down selector.
     *
     * @var string
     */
    private $interfaceLocaleSelect = 'select[name=interface_locale]';

    /**
     * Gets list of locale codes from "Interface Locale" field.
     *
     * @return array of locale codes for example ['en_US', 'de_DE']
     */
    public function getInterfaceLocales()
    {
        $locales = [];
        $selectElement = $this->_rootElement->find($this->interfaceLocaleSelect);
        foreach ($selectElement->getElements('option') as $option) {
            $locales[] = $option->getValue();
        }

        return $locales;
    }
}
