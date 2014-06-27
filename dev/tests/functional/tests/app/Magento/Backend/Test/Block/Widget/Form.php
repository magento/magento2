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
 * @api
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backend\Test\Block\Widget;

use Mtf\Factory\Factory;
use Mtf\Fixture\FixtureInterface;
use Mtf\Client\Element\Locator;
use Mtf\Block\Form as FormInstance;

/**
 * Class Form
 * Is used to represent any form on the page
 *
 */
class Form extends FormInstance
{
    /**
     * 'Save' button
     *
     * @var string
     */
    protected $saveButton = '#save';

    /**
     * 'Save And Continue Edit' button
     *
     * @var string
     */
    protected $saveAndContinueButton = '#save_and_continue';

    /**
     * 'Save And Continue Edit' button
     *
     * @var string
     */
    protected $saveAndContinueEditButton = '#save_and_continue_edit';

    /**
     * Back button
     *
     * @var string
     */
    protected $backButton = '#back';

    /**
     * Reset button
     *
     * @var string
     */
    protected $resetButton = '#reset';

    /**
     * 'Delete' button
     *
     * @var string
     */
    protected $deleteButton = '#delete-button-button';

    /**
     * Backend abstract block
     *
     * @var string
     */
    protected $templateBlock = './ancestor::body';

    /**
     * Selector of element to wait for. If set by child will wait for element after action
     *
     * @var string
     */
    protected $waitForSelector;

    /**
     * Locator type of waitForSelector
     *
     * @var Locator
     */
    protected $waitForSelectorType = Locator::SELECTOR_CSS;

    /**
     * Wait for should be for visibility or not?
     *
     * @var boolean
     */
    protected $waitForSelectorVisible = true;

    /**
     * Update the root form
     *
     * @param FixtureInterface $fixture
     * @return Form
     */
    public function update(FixtureInterface $fixture)
    {
        $this->fill($fixture);
        return $this;
    }

    /**
     * Get backend abstract block
     *
     * @return \Magento\Backend\Test\Block\Template
     */
    protected function getTemplateBlock()
    {
        return Factory::getBlockFactory()->getMagentoBackendTemplate(
            $this->_rootElement->find($this->templateBlock, Locator::SELECTOR_XPATH)
        );
    }

    /**
     * Save the form
     *
     * @param FixtureInterface $fixture
     * @return Form
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function save(FixtureInterface $fixture = null)
    {
        $this->_rootElement->find($this->saveButton, Locator::SELECTOR_CSS)->click();
        $this->waitForElement();
        return $this;
    }

    /**
     * Method that waits for the configured selector using class attributes.
     */
    protected function waitForElement()
    {
        if (!empty($this->waitForSelector)) {
            if ($this->waitForSelectorVisible) {
                $this->getTemplateBlock()->waitForElementVisible($this->waitForSelector, $this->waitForSelectorType);
            } else {
                $this->getTemplateBlock()->waitForElementNotVisible($this->waitForSelector, $this->waitForSelectorType);
            }
        }
    }

    /**
     * Back action
     *
     * @return Form
     */
    public function back()
    {
        $this->_rootElement->find($this->backButton, Locator::SELECTOR_CSS)->click();
        return $this;
    }

    /**
     * Reset the form
     *
     * @return Form
     */
    public function reset()
    {
        $this->_rootElement->find($this->resetButton, Locator::SELECTOR_CSS)->click();
        return $this;
    }

    /**
     * Delete current form item
     *
     * @param FixtureInterface $fixture
     * @return Form
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete(FixtureInterface $fixture = null)
    {
        $this->_rootElement->find($this->deleteButton, Locator::SELECTOR_CSS)->click();
        return $this;
    }

    /**
     * Click save and continue button on form
     */
    public function clickSaveAndContinue()
    {
        $this->_rootElement->find($this->saveAndContinueButton, Locator::SELECTOR_CSS)->click();
        return $this;
    }

    /**
     * Click save and continue button on form
     */
    public function clickSaveAndContinueEdit()
    {
        $this->_rootElement->find($this->saveAndContinueEditButton, Locator::SELECTOR_CSS)->click();
        return $this;
    }
}
