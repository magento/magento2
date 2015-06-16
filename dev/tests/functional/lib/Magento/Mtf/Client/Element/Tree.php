<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Client\Element;

use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Client\Locator;

/**
 * General class for tree elements. Holds general implementation of methods, which overrides in child classes.
 */
abstract class Tree extends SimpleElement
{
    /**
     * Selected checkboxes.
     *
     * @var string
     */
    protected $selectedLabels;

    /**
     * Pattern for child element node.
     *
     * @var string
     */
    protected $pattern;

    /**
     * Selector for child loader.
     *
     * @var string
     */
    protected $childLoader = 'ul';

    /**
     * Selector for input.
     *
     * @var string
     */
    protected $input;

    /**
     * Selector for parent element.
     *
     * @var string
     */
    protected $parentElement;

    /**
     * Display children.
     *
     * @param string $element
     * @return void
     */
    abstract protected function displayChildren($element);

    /**
     * Get element label.
     *
     * @param ElementInterface $element
     * @return string
     */
    abstract protected function getElementLabel(ElementInterface $element);

    /**
     * Drag and drop element to(between) another element(s).
     *
     * @param ElementInterface $target
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function dragAndDrop(ElementInterface $target)
    {
        throw new \Exception('Not applicable for this class of elements (TreeElement)');
    }

    /**
     * keys method is not accessible in this class.
     * Throws exception if used.
     *
     * @param array $keys
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function keys(array $keys)
    {
        throw new \Exception('Not applicable for this class of elements (TreeElement)');
    }

    /**
     * Click a tree element by its path (Node names) in tree.
     *
     * @param string $path
     */
    public function setValue($path)
    {
        $this->eventManager->dispatchEvent(['set_value'], [(string)$this->getAbsoluteSelector()]);
        $elementSelector = $this->prepareElementSelector($path);
        $elements = $this->getElements('.' . $elementSelector . $this->input, Locator::SELECTOR_XPATH);
        foreach ($elements as $element) {
            $element->click();
        }
    }

    /**
     * Get the value.
     *
     * @return array
     */
    public function getValue()
    {
        $this->eventManager->dispatchEvent(['get_value'], [(string)$this->getAbsoluteSelector()]);
        $checkboxes = $this->getElements($this->selectedLabels, Locator::SELECTOR_XPATH);

        return $this->prepareValues($checkboxes);
    }

    /**
     * Prepare values for checked checkboxes.
     *
     * @param ElementInterface[] $checkboxes
     * @return array
     */
    protected function prepareValues(array $checkboxes)
    {
        $values = [];
        foreach ($checkboxes as $checkbox) {
            $fullPath = $this->getFullPath($checkbox);
            $values[] = implode('/', array_reverse($fullPath));
        }

        return $values;
    }

    /**
     * Prepare element selector.
     *
     * @param string $path
     * @return string
     */
    protected function prepareElementSelector($path)
    {
        $pathArray = explode('/', $path);
        $elementSelector = '';
        foreach ($pathArray as $itemElement) {
            $this->displayChildren($itemElement);
            $elementSelector .= sprintf($this->pattern, $itemElement);
        }

        return $elementSelector;
    }

    /**
     * Check visible element.
     *
     * @param string $path
     * @return bool
     */
    public function isElementVisible($path)
    {
        $elementSelector = $this->prepareElementSelector($path);
        return $this->find($elementSelector, Locator::SELECTOR_XPATH)->isVisible();
    }

    /**
     * Waiter for load children.
     *
     * @param ElementInterface $element
     * @return void
     */
    protected function waitLoadChildren(ElementInterface $element)
    {
        $selector = $this->childLoader;
        $this->waitUntil(
            function () use ($element, $selector) {
                return $element->find($selector)->isVisible() ? true : null;
            }
        );
    }

    /**
     * Get full path for element.
     *
     * @param ElementInterface $element
     * @return string[]
     */
    protected function getFullPath(ElementInterface $element)
    {
        $fullPath[] = $this->getElementLabel($element);
        $parentElement = $element->find($this->parentElement, Locator::SELECTOR_XPATH);
        if ($parentElement->isVisible()) {
            $fullPath = array_merge($fullPath, $this->getFullPath($parentElement));
        }

        return $fullPath;
    }
}
