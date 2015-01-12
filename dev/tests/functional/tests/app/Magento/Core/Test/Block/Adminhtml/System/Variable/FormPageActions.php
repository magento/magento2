<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Core\Test\Block\Adminhtml\System\Variable;

use Magento\Backend\Test\Block\FormPageActions as AbstractFormPageActions;
use Mtf\Client\Element\Locator;

/**
 * Class FormPageActions
 * Page Actions for Custom Variable
 */
class FormPageActions extends AbstractFormPageActions
{
    /**
     * "Save and Continue Edit" button
     *
     * @var string
     */
    protected $saveAndContinueButton = '#save_and_edit';

    /**
     * Store View button
     *
     * @var string
     */
    protected $storeViewButton = '.store-switcher .toggle';

    /**
     * Store View locator
     *
     * @var string
     */
    protected $storeView = './/*/a[contains(text(),"%s")]';

    /**
     * Select Store View
     *
     * @param string $storeName
     * @throws \Exception
     * @return void|bool
     */
    public function selectStoreView($storeName)
    {
        $languageSwitcher = $this->_rootElement->find($this->storeViewButton);
        if (!$languageSwitcher->isVisible()) {
            return false;
        }
        $languageSwitcher->click();
        $selector = sprintf($this->storeView, $storeName);
        $storeView = $this->_rootElement->find($selector, Locator::SELECTOR_XPATH);
        if ($storeView->isVisible()) {
            $storeView->click();
        } else {
            throw new \Exception('Store View with name \'' . $storeName . '\' is not visible!');
        }
        $this->_rootElement->acceptAlert();
    }
}
