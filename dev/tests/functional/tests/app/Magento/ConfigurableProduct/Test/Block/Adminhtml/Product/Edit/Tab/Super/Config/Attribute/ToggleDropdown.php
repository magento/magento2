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

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config\Attribute;

use Mtf\Client\Element\Locator;
use Mtf\Client\Driver\Selenium\Element;

/**
 * Class ToggleDropdown
 * Class for toggle dropdown elements.
 */
class ToggleDropdown extends Element
{
    /**
     * Selector for field value
     *
     * @var string
     */
    protected $field = './/button/span';

    /**
     * Selector for list options
     *
     * @var string
     */
    protected $listOptions = './/ul[@data-role="dropdown-menu"]';

    /**
     * Selector for search option by text
     *
     * @var string
     */
    protected $optionByText = './/ul[@data-role="dropdown-menu"]/li/a[.="%s"]';

    /**
     * Set value
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $this->_eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);

        if ($value != $this->getValue()) {
            $value = ('Yes' == $value) ? '%' : '$';

            $this->find($this->field, Locator::SELECTOR_XPATH)->click();
            $this->waitListOptionsVisible();
            $this->find(sprintf($this->optionByText, $value), Locator::SELECTOR_XPATH)->click();
        }
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        $this->_eventManager->dispatchEvent(['get_value'], [(string)$this->_locator]);

        $value = $this->find($this->field, Locator::SELECTOR_XPATH)->getText();
        return ('%' == $value) ? 'Yes' : 'No';
    }

    /**
     * Wait visible list options
     *
     * @return void
     */
    protected function waitListOptionsVisible()
    {
        $browser = $this;
        $selector = $this->listOptions;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                return $browser->find($selector, Locator::SELECTOR_XPATH)->isVisible() ? true : null;
            }
        );
    }
}
