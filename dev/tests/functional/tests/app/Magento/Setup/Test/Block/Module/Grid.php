<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Block\Module;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;

/**
 * Class Grid
 */
class Grid extends Block
{
    /**
     * Select action toggle.
     *
     * @var string
     */
    protected $selectAction = '.action-select';

    /**
     * Module name path.
     *
     * @var string
     */
    protected $componentName = '//table/tbody/tr/td/span[contains(text(), \'#placeholder#\')]';

    /**
     * Select path.
     *
     * @var string
     */
    protected $select = '//table[contains(@class, \'data-grid\')]//*//span[contains(text(), \'#placeholder#\')]//..//..//td//div[contains(@class, \'action-select\')]';

    /**
     * Next button selector.
     *
     * @var string
     */
    protected $next = '.action-next';

    /**
     * Item enable selector.
     *
     * @var string
     */
    protected $itemEnable = '.item-enable';

    /**
     * Item disable selector.
     *
     * @var string
     */
    protected $itemDisable = '.item-disable';

    /**
     * Click Next button.
     *
     * @return void
     */
    public function clickNext()
    {
        $this->_rootElement->find($this->next, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Check if Next page button is available.
     *
     * @return bool
     */
    public function isClickNextAvailable()
    {
        return !$this->_rootElement->find($this->next, Locator::SELECTOR_CSS)->isDisabled();
    }

    /**
     * Find module by it's name.
     *
     * @param string $name
     * @return \Magento\Mtf\Client\ElementInterface
     */
    public function findModuleByName($name)
    {
        $element = $this->getModuleByName($name);

        while (!$element->isVisible() && $this->isClickNextAvailable()) {
            $this->clickNext();

            $element = $this->getModuleByName($name);
        }

        return $element;
    }

    /**
     * Retrieve module by it's name.
     *
     * @param string $name
     * @return \Magento\Mtf\Client\ElementInterface
     */
    private function getModuleByName($name)
    {
        $componentName = str_replace('#placeholder#', $name, $this->componentName);

        return $this->_rootElement->find($componentName, Locator::SELECTOR_XPATH);
    }

    /**
     * Check if Module is enabled.s
     *
     * @param string $name
     * @return bool
     */
    public function isModuleEnabled($name)
    {
        $element = $this->findModuleByName($name);
        $select = str_replace('#placeholder#', $name, $this->select);

        return $element->find($select, Locator::SELECTOR_XPATH)->find('.item-disable')->isVisible();
    }

    /**
     * Disable Module.
     *
     * @param string $name
     */
    public function disableModule($name)
    {
        $element = $this->findModuleByName($name);
        $select = str_replace('#placeholder#', $name, $this->select);

        $element->find($select, Locator::SELECTOR_XPATH)->find($this->itemDisable)->click();
    }

    /**
     * Enable Module.
     *
     * @param $name
     */
    public function enableModule($name)
    {
        $element = $this->findModuleByName($name);
        $select = str_replace('#placeholder#', $name, $this->select);

        $element->find($select, Locator::SELECTOR_XPATH)->find($this->itemEnable)->click();
    }
}