<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Block\Module;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\ElementInterface;

/**
 * Class Grid
 *
 * Table grid on backend.
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
    protected $componentName = '//table//*//div[contains(text(), \'%s\')]';

    /**
     * Select path.
     *
     * @var string
     */
    protected $select = '//div[contains(text(), \'%s\')]//..//..//td//div[contains(@class, \'action-select\')]';

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
     * Button element.
     *
     * @var string
     */
    protected $button = 'button';

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
     * @return ElementInterface
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
     * @return ElementInterface
     */
    private function getModuleByName($name)
    {
        $componentName = sprintf($this->componentName, $name);

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
        $select = sprintf($this->select, $name);

        $element->find($select, Locator::SELECTOR_XPATH)->find($this->button)->click();
        $isVisible = $element->find($select, Locator::SELECTOR_XPATH)->find($this->itemDisable)->isVisible();
        $element->find($select, Locator::SELECTOR_XPATH)->find($this->button)->click();

        return $isVisible;
    }

    /**
     * Disable Module.
     *
     * @param string $name
     * @return void
     */
    public function disableModule($name)
    {
        $element = $this->findModuleByName($name);
        $select = sprintf($this->select, $name);

        $element->find($select, Locator::SELECTOR_XPATH)->find($this->button)->click();
        $element->find($select, Locator::SELECTOR_XPATH)->find($this->itemDisable)->click();
    }

    /**
     * Enable Module.
     *
     * @param string $name
     * @return void
     */
    public function enableModule($name)
    {
        $element = $this->findModuleByName($name);
        $select = sprintf($this->select, $name);

        $element->find($select, Locator::SELECTOR_XPATH)->find($this->button)->click();
        $element->find($select, Locator::SELECTOR_XPATH)->find($this->itemEnable)->click();
    }
}
