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

namespace Mtf\Client\Driver\Selenium\Element;

use Mtf\Client\Driver\Selenium\Element;
use Mtf\Client\Element\Locator;

/**
 * Class LiselectElement
 * Typified element class for lists selectors
 *
 */
class LiselectElement extends Element
{
    /**
     * Template for each element of option
     *
     * @var string
     */
    protected $optionMaskElement = 'li[*[contains(text(), "%s")]]';

    /**
     * Additional part for each child element of option
     *
     * @var string
     */
    protected $optionMaskFollowing = '/following-sibling::';

    /**
     * Select value in liselect dropdown
     *
     * @param array $value
     * @throws \Exception
     */
    public function setValue($value)
    {
        $this->_context->find('.toggle', Locator::SELECTOR_CSS)->click();

        $optionSelector = array();
        foreach ($value as $key => $option) {
            $optionSelector[] = sprintf($this->optionMaskElement, $value[$key]);
        }
        $optionSelector = './/' . implode($this->optionMaskFollowing, $optionSelector) . '/a';

        $option = $this->_context->find($optionSelector, Locator::SELECTOR_XPATH);
        if (!$option->isVisible()) {
            throw new \Exception('[' . implode('/', $value) . '] option is not visible in store switcher.');
        }
        $option->click();
    }
}
