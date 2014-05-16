<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Tax\Test\Block\Adminhtml\Rule\Edit;

use Mtf\Fixture\FixtureInterface;
use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

/**
 * Class TaxClass
 * Customer/Product Tax Classes block
 *
 */
class TaxClass extends Block
{
    /**
     * Tax class row item
     *
     * @var string
     */
    protected $taxClassRow = './/label[input[contains(@class, "mselect-checked")]]';

    /**
     * Add new tax class button
     *
     * @var string
     */
    protected $addNewTaxClass = '.action-add';

    /**
     * Tax class to select
     *
     * @var string
     */
    protected $taxClassItem = './/*[contains(@class, "mselect-list-item")]//span';

    /**
     * New tax class input field
     *
     * @var string
     */
    protected $newTaxClass = '.mselect-input';

    /**
     * Save new tax class
     *
     * @var string
     */
    protected $saveTaxClass = '.mselect-save';

    /**
     * Select Tax Class in multiselect and create new one if required
     *
     * @param array $taxClasses
     */
    public function selectTaxClass($taxClasses)
    {
        //Uncheck all marked classes
        while ($this->_rootElement->find($this->taxClassRow, Locator::SELECTOR_XPATH)->isVisible()) {
            $this->_rootElement->find($this->taxClassRow, Locator::SELECTOR_XPATH)->click();
        }
        //Select tax classes
        foreach ($taxClasses as $class) {
            $taxOption = $this->_rootElement->find(
                $this->taxClassItem . '[text()="' . $class . '"]',
                Locator::SELECTOR_XPATH
            );
            if (!$taxOption->isVisible()) {
                $this->_rootElement->find($this->addNewTaxClass, Locator::SELECTOR_CSS)->click();
                $this->_rootElement->find($this->newTaxClass, Locator::SELECTOR_CSS)->setValue($class);
                $this->_rootElement->find($this->saveTaxClass, Locator::SELECTOR_CSS)->click();
                $this->waitForElementVisible(
                    $this->taxClassRow . '/span[text()="' . $class . '"]',
                    Locator::SELECTOR_XPATH
                );
            } else {
                $this->_rootElement->find('//label/span[text()="' . $class . '"]', Locator::SELECTOR_XPATH)->click();
            }
        }
    }
}
