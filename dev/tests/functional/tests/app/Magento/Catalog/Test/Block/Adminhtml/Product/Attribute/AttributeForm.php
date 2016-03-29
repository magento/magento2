<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute;

use Magento\Ui\Test\Block\Adminhtml\FormSections;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Edit attribute form on catalog product edit page.
 */
class AttributeForm extends FormSections
{
    /**
     * Save button selector.
     *
     * @var string
     */
    protected $saveButton = '#save';

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
