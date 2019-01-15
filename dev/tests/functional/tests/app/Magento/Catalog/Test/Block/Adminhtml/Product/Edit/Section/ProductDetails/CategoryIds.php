<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\ProductDetails;

use Magento\Mtf\Client\Element\MultisuggestElement;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\DriverInterface;
use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\System\Event\EventManagerInterface;

/**
 * Typified element class for category element.
 */
class CategoryIds extends MultisuggestElement
{
    /**
     * Selector item of search result.
     *
     * @var string
     */
    protected $resultItem = './/label[contains(@class, "admin__action-multiselect-label")]/span[text() = "%s"]';

    /**
     * Browser instance.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Locator for page footer.
     *
     * @var string
     */
    protected $pageFooter = '.page-footer';

    /**
     * Locator for advanced inventory button.
     *
     * @var string
     */
    protected $advancedInventoryButton = '[data-index="advanced_inventory_button"]';

    /**
     * @constructor
     * @param BrowserInterface $browser
     * @param DriverInterface $driver
     * @param EventManagerInterface $eventManager
     * @param Locator $locator
     * @param ElementInterface $context
     */
    public function __construct(
        BrowserInterface $browser,
        DriverInterface $driver,
        EventManagerInterface $eventManager,
        Locator $locator,
        ElementInterface $context = null
    ) {
        $this->browser = $browser;
        parent::__construct($driver, $eventManager, $locator, $context);
    }

    /**
     * Set category value.
     *
     * @param array|string $values
     * @return void
     */
    public function setValue($values)
    {
        // Align Category ids select element to the center of the browser for created categories
        if ($this->browser->find($this->pageFooter)->isVisible()) {
            $this->browser->find($this->pageFooter)->hover();
            $this->browser->find($this->advancedInventoryButton)->hover();
        }
        $this->eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);

        $this->clear();
        foreach ((array)$values as $value) {
            if (!$this->isChoice($value)) {
                if ($value == '') {
                    continue;
                }

                $this->keys([$value]);
                $searchedItem = $this->find(sprintf($this->resultItem, $value), Locator::SELECTOR_XPATH);
                $searchedCountElements = $this->find($this->searchedCount);
                $this->waitUntil(
                    function () use ($searchedCountElements) {
                        return $searchedCountElements->isVisible() ? true : null;
                    }
                );
                $searchedItem->click();

                $closeButton = $this->find($this->closeButton);
                if ($closeButton->isVisible()) {
                    $closeButton->click();
                }
            }
        }
    }
}
