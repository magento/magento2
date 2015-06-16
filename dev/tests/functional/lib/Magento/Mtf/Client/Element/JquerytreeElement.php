<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Client\Element;

use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Client\Locator;

/**
 * Typified element class for JqueryTree elements.
 */
class JquerytreeElement extends Tree
{
    /**
     * Root element.
     *
     * @var string
     */
    protected $rootElement = '//div[contains(@class, "tree x-tree jstree")]';

    /**
     * Pattern for level node.
     *
     * @var string
     */
    protected $level = '/ul/li[contains(@class, "jstree")]';

    /**
     * Pattern for child element node.
     *
     * @var string
     */
    protected $pattern = '/ul/li[contains(@class, "jstree") and a[text() = "%s"]]';

    /**
     * Pattern for child open node.
     *
     * @var string
     */
    protected $openNode = '//li[contains(@class, "jstree-open") and a[text() = "%s"]]';

    /**
     * Pattern for child closed node.
     *
     * @var string
     */
    protected $closedNode = '//li[contains(@class, "jstree-closed") and a[text() = "%s"]]';

    /**
     * Selector for parent element.
     *
     * @var string
     */
    protected $parentElement = './../../../a';

    /**
     * Selector for input.
     *
     * @var string
     */
    protected $input = '/a/ins[@class="jstree-checkbox"]';

    /**
     * Selected checkboxes.
     *
     * @var string
     */
    protected $selectedLabels = '//li[contains(@class, "jstree-checked")]/a';

    /**
     * Selected checkboxes by level.
     *
     * @var string
     */
    protected $selectedLabelsByLevel = '/ul/li[contains(@class, "jstree-checked")]/a';

    /**
     * Display children.
     *
     * @param string $element
     * @return void
     */
    protected function displayChildren($element)
    {
        $element = $this->find(sprintf($this->openNode, $element), Locator::SELECTOR_XPATH);
        if ($element->isVisible()) {
            return;
        }
        $plusButton = $this->find(sprintf($this->closedNode, $element) . $this->input, Locator::SELECTOR_XPATH);
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
        return trim($element->getText());
    }

    /**
     * Get structure.
     *
     * @param int|null $level
     * @return array
     */
    public function getStructure($level = null)
    {
        $nodesSelector = $this->getNodesSelector($level);
        $nodes = $this->getElements($nodesSelector, Locator::SELECTOR_XPATH);

        return $this->prepareValues($nodes);
    }

    /**
     * Get nodes selector.
     *
     * @param int|null $level
     * @return string
     */
    protected function getNodesSelector($level)
    {
        $selector = $this->rootElement;
        if ($level !== null) {
            for ($i = 1; $i < $level; $i++) {
                $selector .= $this->level;
            }
            $selector .= $this->selectedLabelsByLevel;
        } else {
            $selector .= $this->selectedLabels;
        }

        return $selector;
    }
}
