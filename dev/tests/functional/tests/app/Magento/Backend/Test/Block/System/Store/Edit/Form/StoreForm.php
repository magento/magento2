<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\System\Store\Edit\Form;

use Mtf\Block\Form;
use Mtf\Client\Element\Locator;

/**
 * Class StoreForm
 * Form for Store View creation
 */
class StoreForm extends Form
{
    /**
     * Store name selector in dropdown
     *
     * @var string
     */
    protected $store = '//option[contains(.,"%s")]';

    /**
     * Check that Store visible in Store dropdown
     *
     * @param string $name
     * @return bool
     */
    public function isStoreVisible($name)
    {
        return $this->_rootElement->find(sprintf($this->store, $name), Locator::SELECTOR_XPATH)->isVisible();
    }
}
