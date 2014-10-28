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
 * Class MultisuggestElement
 * Typified element class for multi suggest element
 */
class MultisuggestElement extends SuggestElement
{
    /**
     * Selector list choice
     *
     * @var string
     */
    protected $listChoice = './/ul[contains(@class,"mage-suggest-choices")]';

    /**
     * Selector choice item
     *
     * @var string
     */
    protected $choice = './/li/div[text()="%s"]/..';

    /**
     * Selector choice value
     *
     * @var string
     */
    protected $choiceValue = './/li[contains(@class,"mage-suggest-choice")]/div';

    /**
     * Selector remove choice item
     *
     * @var string
     */
    protected $choiceClose = '.mage-suggest-choice-close';

    /**
     * Set value
     *
     * @param array|string $values
     * @return void
     */
    public function setValue($values)
    {
        $this->_eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);

        $this->clear();
        foreach ((array)$values as $value) {
            if (!$this->isChoice($value)) {
                parent::setValue($value);
            }
        }
    }

    /**
     * Get value
     *
     * @return array
     */
    public function getValue()
    {
        $this->_eventManager->dispatchEvent(['get_value'], [(string) $this->_locator]);

        $listChoice = $this->find($this->listChoice, Locator::SELECTOR_XPATH);
        $choices = $listChoice->find($this->choiceValue, Locator::SELECTOR_XPATH)->getElements();
        $values = [];

        foreach ($choices as $choice) {
            /** @var Element $choice */
            $values[] = $choice->getText();
        }
        return $values;
    }

    /**
     * Check exist selected item
     *
     * @param string $value
     * @return bool
     */
    protected function isChoice($value)
    {
        return $this->find(sprintf($this->choice, $value), Locator::SELECTOR_XPATH)->isVisible();
    }

    /**
     * Clear element
     *
     * @return void
     */
    protected function clear()
    {
        $choiceClose = $this->find($this->choiceClose);
        while ($choiceClose->isVisible()) {
            $choiceClose->click();
            $choiceClose = $this->find($this->choiceClose);
        }
    }
}
