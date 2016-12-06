<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\System\Store\Edit\Form;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

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
     * Store dropdown selector
     *
     * @var string
     */
    protected $storeGroupId = '//select[@id="store_group_id"]';

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

    /**
     * Select store view by name from dropdown
     *
     * @param string $name
     * @return bool
     */
    public function selectStore($name)
    {
        return $this->_rootElement->find($this->storeGroupId, Locator::SELECTOR_XPATH, 'optgroupselect')
            ->setValue($name);
    }
}
