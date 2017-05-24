<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Block\Adminhtml\Block\Edit;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;
use Magento\Cms\Test\Block\Adminhtml\Wysiwyg\Config;

/**
 * Block adminhtml form.
 */
class BlockForm extends Form
{
    /**
     * Content Editor toggle button id.
     *
     * @var string
     */
    protected $toggleButton = "#toggleblock_content";

    /**
     * Content Editor form.
     *
     * @var string
     */
    protected $contentForm = "#page_content";

    /**
     * Custom Variable block selector.
     *
     * @var string
     */
    protected $customVariableBlock = "./ancestor::body//div[div[@id='variables-chooser']]";

    /**
     * Insert Variable button selector.
     *
     * @var string
     */
    protected $addVariableButton = ".add-variable";

    /**
     * Clicking in content tab 'Insert Variable' button.
     *
     * @return void
     */
    public function clickInsertVariable()
    {
        $addVariableButton = $this->_rootElement->find($this->addVariableButton);
        if ($addVariableButton->isVisible()) {
            $addVariableButton->click();
        }
    }

    /**
     * Get for wysiwyg config block.
     *
     * @return Config
     */
    public function getWysiwygConfig()
    {
        return $this->blockFactory->create(
            \Magento\Cms\Test\Block\Adminhtml\Wysiwyg\Config::class,
            ['element' => $this->_rootElement->find($this->customVariableBlock, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Page Content Show/Hide Editor toggle button.
     *
     * @return void
     */
    public function toggleEditor()
    {
        $content = $this->_rootElement->find($this->contentForm, Locator::SELECTOR_CSS);
        $toggleButton = $this->_rootElement->find($this->toggleButton, Locator::SELECTOR_CSS);
        if (!$content->isVisible() && $toggleButton->isVisible()) {
            $toggleButton->click();
        }
    }
}
