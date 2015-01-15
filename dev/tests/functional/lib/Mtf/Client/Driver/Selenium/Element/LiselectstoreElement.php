<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mtf\Client\Driver\Selenium\Element;

use Mtf\Client\Driver\Selenium\Element;
use Mtf\Client\Element\Locator;

/**
 * Class LiselectstoreElement
 * Typified element class for lists selectors
 */
class LiselectstoreElement extends Element
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
     * Website selector
     *
     * @var string
     */
    protected $websiteSelector = '(.//li[a[@data-role="website-id"]])[%d]';

    /**
     * Store selector
     *
     * @var string
     */
    protected $storeSelector = '(.//li[@class = "store-switcher-store disabled"])[%d]';

    /**
     * StoreView selector
     *
     * @var string
     */
    protected $storeViewSelector = './/li[a[@data-role="store-view-id"]]';

    /**
     * Toggle element selector
     *
     * @var string
     */
    protected $toggleSelector = '.toggle';

    /**
     * Select value in liselect dropdown
     *
     * @param string $value
     * @throws \Exception
     */
    public function setValue($value)
    {
        $this->_eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);
        $this->_context->find($this->toggleSelector)->click();

        $value = explode('/', $value);
        $optionSelector = [];
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

    /**
     * Get all li elements from dropdown
     *
     * @return array
     */
    protected function getLiElements()
    {
        $this->_context->find($this->toggleSelector)->click();
        $criteria = new \PHPUnit_Extensions_Selenium2TestCase_ElementCriteria('css selector');
        $criteria->value('li');
        $elements = $this->_getWrappedElement()->elements($criteria);
        $dropdownData = [];
        foreach ($elements as $element) {
            $class = $element->attribute('class');
            $dropdownData[] = [
                'element' => $element,
                'storeView' => $this->isSubstring($class, "store-switcher-store-view"),
                'store' => $this->isSubstring($class, "store-switcher-store "),
                'website' => $this->isSubstring($class, "store-switcher-website"),
                'current' => $this->isSubstring($class, "current"),
                'default_config' => $this->isSubstring($class, "store-switcher-all"),
            ];
        }
        return $dropdownData;
    }

    /**
     * Get all available store views
     *
     * @return array
     */
    public function getValues()
    {
        $dropdownData = $this->getLiElements();
        $data = [];
        foreach ($dropdownData as $key => $dropdownElement) {
            if ($dropdownElement['storeView']) {
                $data[] = $this->findNearestElement('website', $key, $dropdownData) . "/"
                    . $this->findNearestElement('store', $key, $dropdownData) . "/"
                    . $dropdownElement['element']->text();
            }
        }
        return $data;
    }

    /**
     * Check if string contains substring
     *
     * @param string $haystack
     * @param string $pattern
     * @return bool
     */
    protected function isSubstring($haystack, $pattern)
    {
        return preg_match("/$pattern/", $haystack) != 0 ? true : false;
    }

    /**
     * Return nearest elements name according to criteria
     *
     * @param string $criteria
     * @param string $key
     * @param array $elements
     * @return bool
     */
    protected function findNearestElement($criteria, $key, array $elements)
    {
        $elementText = false;
        while ($elementText == false) {
            $elementText = $elements[$key][$criteria] == true ? $elements[$key]['element']->text() : false;
            $key--;
        }
        return $elementText;
    }

    /**
     * Get selected store value
     *
     * @throws \Exception
     * @return string
     */
    public function getValue()
    {
        $this->_eventManager->dispatchEvent(['get_value'], [(string)$this->_locator]);
        $elements = $this->getLiElements();
        foreach ($elements as $key => $element) {
            if ($element['current'] == true) {
                if ($element['default_config'] == true) {
                    return $element['element']->text();
                }
                $path = $this->findNearestElement('website', $key, $elements) . "/"
                    . $this->findNearestElement('store', $key, $elements) . "/"
                    . $element['element']->text();
                return $path;
            }
        }
    }
}
