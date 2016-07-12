<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Block\Modules;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;

class Grid extends Block
{
    /**
     * Select action toggle.
     *
     * @var string
     */
    protected $selectAction = '.action-select';

    /**
     * Module name xPath.
     *
     * @var string
     */
    protected $componentName = '//table/tbody/tr[2]/td[2]/span[contains(text(), \'#placeholder#\')]';

    protected $disableModule = '//table[contains(@class, \'data-grid\')]//tr//td//span[contains(text(), \'#placeholder#\')]//..//..//td//div[contains(@class, \'action-select\')]//button';

    protected $next = '.action-next';

    protected function clickActionsGrid(SimpleElement $rowItem)
    {
        $rowItem->find($this->selectAction)->click();
    }

    /**
     * @return void
     */
    public function clickNext()
    {
        $this->_rootElement->find($this->next, Locator::SELECTOR_CSS)->click();
    }

    /**
     * @return bool
     */
    public function isClickNextAvailable()
    {
        return !$this->_rootElement->find($this->next, Locator::SELECTOR_CSS)->isDisabled();
    }

    /**
     * @param string $name
     * @return \Magento\Mtf\Client\ElementInterface
     */
    public function findByName($name)
    {
        $element = $this->getByName($name);

        while (!$element->isVisible() && $this->isClickNextAvailable()) {
            $this->clickNext();

            $element = $this->getByName($name);
        }

        return $element;
    }

    /**
     * @param string $name
     * @return \Magento\Mtf\Client\ElementInterface
     */
    private function getByName($name)
    {
        $componentName = str_replace('#placeholder#', $name, $this->componentName);

        return $this->_rootElement->find($componentName, Locator::SELECTOR_XPATH);
    }

    public function disableModule($name)
    {
        $componentName = str_replace('#placeholder#', $name, $this->disableModule);

        $this->_rootElement->find($componentName, Locator::SELECTOR_XPATH)->click();
    }
}