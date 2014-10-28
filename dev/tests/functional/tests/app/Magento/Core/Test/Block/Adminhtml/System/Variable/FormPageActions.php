<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    protected $storeViewButton = '[data-ui-id="language-switcher"] .toggle';

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
