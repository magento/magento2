<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Client\Element;

use Magento\Mtf\Client\Locator;

/**
 * Class LiselectstoreElement
 * Typified element class for lists selectors
 */
class LiselectstoreElement extends SimpleElement
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
    protected $toggleSelector = '.admin__action-dropdown[data-toggle="dropdown"]';

    /**
     * Select value in liselect dropdown
     *
     * @param string $value
     * @throws \Exception
     */
    public function setValue($value)
    {
        $this->eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);
        $this->context->find($this->toggleSelector)->click();

        $value = explode('/', $value);
        $optionSelector = [];
        foreach ($value as $key => $option) {
            $optionSelector[] = sprintf($this->optionMaskElement, $value[$key]);
        }
        $optionSelector = './/' . implode($this->optionMaskFollowing, $optionSelector) . '/a';

        $option = $this->context->find($optionSelector, Locator::SELECTOR_XPATH);
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
        $this->find($this->toggleSelector)->click();
        $elements = $this->driver->getElements($this, 'li', Locator::SELECTOR_TAG_NAME);
        $dropdownData = [];
        foreach ($elements as $element) {
            $class = $element->getAttribute('class');
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
                    . $dropdownElement['element']->getText();
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
            $elementText = $elements[$key][$criteria] == true ? $elements[$key]['element']->getText() : false;
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
        $this->eventManager->dispatchEvent(['get_value'], [$this->getAbsoluteSelector()]);
        $elements = $this->getLiElements();
        foreach ($elements as $key => $element) {
            if ($element['current'] == true) {
                if ($element['default_config'] == true) {
                    return $element['element']->getText();
                }
                $path = $this->findNearestElement('website', $key, $elements) . "/"
                    . $this->findNearestElement('store', $key, $elements) . "/"
                    . $element['element']->getText();
                return $path;
            }
        }

        return '';
    }
}
