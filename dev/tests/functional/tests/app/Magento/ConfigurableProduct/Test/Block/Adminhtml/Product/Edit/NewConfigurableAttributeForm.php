<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit;

use Magento\Ui\Test\Block\Adminhtml\FormSections;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * New attribute form on configurable product page.
 */
class NewConfigurableAttributeForm extends FormSections
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
     * Attribute to check whether section is opened.
     *
     * @var string
     */
    private $isSectionOpened = 'active';

    /**
     * Fill the attribute form.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $this->browser->switchToFrame(new Locator($this->iFrame));
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
     * Open section.
     *
     * @param string $sectionName
     * @return FormSections
     */
    public function openSection($sectionName)
    {
        $selector = $this->getContainerElement($sectionName)->getLocator()['value'];
        $strategy = null !== $this->getContainerElement($sectionName)->getLocator()['using']
            ? $this->getContainerElement($sectionName)->getLocator()['using']
            : Locator::SELECTOR_CSS;
        $sectionClass = $this->_rootElement->find($selector, $strategy)->getAttribute('class');
        if (strpos($sectionClass, $this->isSectionOpened) === false) {
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
        $this->browser->switchToFrame(new Locator($this->iFrame));
        $this->browser->find($this->saveButton)->click();
        $this->browser->switchToFrame();
    }
}
