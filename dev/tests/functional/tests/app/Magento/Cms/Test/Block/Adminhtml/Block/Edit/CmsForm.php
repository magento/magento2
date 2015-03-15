<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Block\Adminhtml\Block\Edit;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Form for Cms Block creation.
 */
class CmsForm extends Form
{
    /**
     * Content Editor toggle button id.
     *
     * @var string
     */
    protected $toggleButton = "#toggleblock_content";

    /**
     * CMS Block Content area.
     *
     * @var string
     */
    protected $contentForm = '[name="content"]';

    /**
     * Fill the page form.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $this->hideEditor();
        return parent::fill($fixture, $element);
    }

    /**
     * Hide WYSIWYG editor.
     *
     * @return void
     */
    protected function hideEditor()
    {
        $content = $this->_rootElement->find($this->contentForm);
        $toggleButton = $this->_rootElement->find($this->toggleButton);
        if (!$content->isVisible() && $toggleButton->isVisible()) {
            $toggleButton->click();
        }
    }
}
