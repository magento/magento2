<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Client\Element;

use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Client\Locator;

/**
 * Typified element class for Tree elements.
 */
class TreeElement extends Tree
{
    /**
     * All selected checkboxes.
     *
     * @var string
     */
    protected $selectedCheckboxes = '//input[@checked=""]';

    /**
     * Selector for plus image.
     *
     * @var string
     */
    protected $imagePlus = './div/img[contains(@class, "-plus")]';

    /**
     * Selector for input.
     *
     * @var string
     */
    protected $input = '/div/a/span';

    /**
     * Selector for parent element.
     *
     * @var string
     */
    protected $parentElement = './../../../../../div/a/span';

    /**
     * Selected checkboxes.
     *
     * @var string
     */
    protected $selectedLabels = '//input[@checked=""]/../a/span';

    /**
     * Pattern for child element node.
     *
     * @var string
     */
    protected $pattern = '//li[@class="x-tree-node" and div/a/span[contains(text(),"%s")]]';

    /**
     *  Regular pattern mask for node label.
     *
     * @var string
     */
    protected $regPatternLabel = '`(.+) \(.*`';

    /**
     * Clear data for element.
     *
     * @return void
     */
    public function clear()
    {
        $checkboxes = $this->getElements($this->selectedCheckboxes, Locator::SELECTOR_XPATH, 'checkbox');
        foreach ($checkboxes as $checkbox) {
            $checkbox->setValue('No');
        }
    }

    /**
     * Display children.
     *
     * @param string $element
     * @return void
     */
    protected function displayChildren($element)
    {
        $element = $this->find(sprintf($this->pattern, $element), Locator::SELECTOR_XPATH);
        $plusButton = $element->find($this->imagePlus, Locator::SELECTOR_XPATH);
        if ($plusButton->isVisible()) {
            $plusButton->click();
            $this->waitLoadChildren($element);
        }
    }

    /**
     * Get element label.
     *
     * @param ElementInterface $element
     * @return string
     */
    protected function getElementLabel(ElementInterface $element)
    {
        $value = $element->getText();
        preg_match($this->regPatternLabel, $value, $matches);

        return trim($matches[1]);
    }
}
