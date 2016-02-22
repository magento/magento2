<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute;

use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Mtf\Client\Element;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Edit attribute form on catalog product edit page.
 */
class AttributeForm extends FormTabs
{
    /**
     * Iframe locator.
     *
     * @var string
     */
    protected $iFrame = '#create_new_attribute_container';

    /**
     * Save button selector.
     *
     * @var string
     */
    protected $saveButton = '#save';

    /**
     * Attribute to determine whether tab is opened.
     *
     * @var string
     */
    protected $isTabOpened = '.opened ';

    /**
     * Initialize block. Switch to frame.
     *
     * @return void
     */
    protected function init()
    {
        parent::init();
        $this->browser->switchToFrame(new Locator($this->iFrame));
    }

    /**
     * Fill the attribute form.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $browser = $this->browser;
        $selector = $this->saveButton;
        $this->browser->waitUntil(
            function () use ($browser, $selector) {
                return $browser->find($selector)->isVisible() ? true : null;
            }
        );
        parent::fill($fixture, $element);
        $this->browser->switchToFrame();
    }

    /**
     * Open tab.
     *
     * @param string $tabName
     * @return Tab
     */
    public function openTab($tabName)
    {
        $selector = $this->getTabs()[$tabName]['selector'];
        $strategy = isset($this->getTabs()[$tabName]['strategy'])
            ? $this->getTabs()[$tabName]['strategy']
            : Locator::SELECTOR_CSS;

        $isTabOpened = $this->_rootElement->find($this->isTabOpened . $selector, $strategy);
        if (!$isTabOpened->isVisible()) {
            $this->_rootElement->find($selector, $strategy)->click();
        }

        return $this;
    }

    /**
     * Click on "Save" button.
     *
     * @return void
     */
    public function saveAttributeForm()
    {
        $this->browser->find($this->saveButton)->click();
        $this->browser->switchToFrame();
    }
}
