<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Variable\Test\Block\Adminhtml\System\Variable;

use Magento\Backend\Test\Block\FormPageActions as AbstractFormPageActions;
use Magento\Mtf\Client\Locator;

/**
 * Page Actions for Custom Variable.
 */
class FormPageActions extends AbstractFormPageActions
{
    /**
     * "Save and Continue Edit" button.
     *
     * @var string
     */
    protected $saveAndContinueButton = '#save_and_edit';

    /**
     * Store View button.
     *
     * @var string
     */
    protected $storeViewButton = '.store-switcher .actions button';

    /**
     * Store View locator.
     *
     * @var string
     */
    protected $storeView = './/*/a[contains(text(),"%s")]';

    /**
     * Selector for confirm.
     *
     * @var string
     */
    protected $confirmModal = '.confirm._show[data-role=modal]';

    /**
     * Select Store View.
     *
     * @param string $storeName
     * @throws \Exception
     * @return null|bool
     */
    public function selectStoreView($storeName)
    {
        $storeSwitcher = $this->_rootElement->find($this->storeViewButton);
        if (!$storeSwitcher->isVisible()) {
            return false;
        }
        $storeSwitcher->click();
        $selector = sprintf($this->storeView, $storeName);
        $storeView = $this->_rootElement->find($selector, Locator::SELECTOR_XPATH);
        if ($storeView->isVisible()) {
            $storeView->click();
        } else {
            throw new \Exception('Store View with name \'' . $storeName . '\' is not visible!');
        }
        $element = $this->browser->find($this->confirmModal);
        /** @var \Magento\Ui\Test\Block\Adminhtml\Modal $modal */
        $modal = $this->blockFactory->create('Magento\Ui\Test\Block\Adminhtml\Modal', ['element' => $element]);
        $modal->acceptAlert();

        return null;
    }
}
