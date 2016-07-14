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
     * Module name xPath.
     *
     * @var string
     */
    protected $componentName = '//table/tbody/tr/td/span[contains(text(), \'#placeholder#\')]';

    /**
     * Select
     *
     * @var string
     */
    protected $select = '//table[contains(@class, \'data-grid\')]//tbody//tr//td//span[contains(text(), \'#placeholder#\')]//..//..//td//div[contains(@class, \'action-select\')]';

    /**
     * @var string
     */
    protected $next = '.action-next';

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
     * @param string $name
     * @return \Magento\Mtf\Client\ElementInterface
     */
    private function getModuleByName($name)
    {
        $componentName = str_replace('#placeholder#', $name, $this->componentName);

        return $this->_rootElement->find($componentName, Locator::SELECTOR_XPATH);
    }

    public function isModuleEnabled($name)
    {
        $element = $this->findModuleByName($name);
        $select = str_replace('#placeholder#', $name, $this->select);

        $element->find($select, Locator::SELECTOR_XPATH)->find('button')->click();

        return $element->find($select, Locator::SELECTOR_XPATH)->find('.item-disable')->isVisible();
    }

    public function disableModule($name)
    {
        $element = $this->findModuleByName($name);
        $select = str_replace('#placeholder#', $name, $this->select);

        $element->find($select, Locator::SELECTOR_XPATH)->find('button')->click();
        $element->find($select, Locator::SELECTOR_XPATH)->find('.item-disable')->click();
    }

    public function enableModule($name)
    {
        $element = $this->findModuleByName($name);
        $select = str_replace('#placeholder#', $name, $this->select);

        $element->find($select, Locator::SELECTOR_XPATH)->find('button')->click();
        $element->find($select, Locator::SELECTOR_XPATH)->find('.item-enable')->click();
    }
}