<?php
/**
 * Store configuration edit form.
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Block\System\Account;

use Magento\Mtf\Block\Block;

/**
 * Class Form.
 */
class Form extends Block
{
    /**
     * Interface Locale drop-down selector.
     *
     * @var string
     */
    private $interfaceLocaleSelect = 'select[name=interface_locale]';


    /**
     * @return array of locales
     */
    public function getInterfaceLocaleOptions()
    {
        $locales = [];
        $selectElement = $this->_rootElement->find($this->interfaceLocaleSelect);
        foreach ($selectElement->getElements('option') as $option) {
            $locales[$option->getValue()] = $option->getText();
        }

        return $locales;
    }
}
