<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Block\Adminhtml\Page\Edit;

use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\Mtf\Client\Locator;

/**
 * Backend Cms Page edit page.
 */
class PageForm extends FormTabs
{
    /**
     * Content Editor toggle button id.
     *
     * @var string
     */
    protected $toggleButton = "#togglepage_content";

    /**
     * Content Editor form.
     *
     * @var string
     */
    protected $contentForm = "#page_content";

    /**
     * Page Content Show/Hide Editor toggle button.
     *
     * @return void
     */
    protected function toggleEditor()
    {
        $content = $this->_rootElement->find($this->contentForm, Locator::SELECTOR_CSS);
        $toggleButton = $this->_rootElement->find($this->toggleButton, Locator::SELECTOR_CSS);
        if (!$content->isVisible() && $toggleButton->isVisible()) {
            $toggleButton->click();
        }
    }

    /**
     * Returns array with System Variables.
     *
     * @return array
     */
    public function getSystemVariables()
    {
        $this->openTab('content');
        /** @var \Magento\Cms\Test\Block\Adminhtml\Page\Edit\Tab\Content $contentTab */
        $contentTab = $this->getTab('content');
        /** @var \Magento\Cms\Test\Block\Adminhtml\Wysiwyg\Config $config */
        $contentTab->clickInsertVariable();
        $config = $contentTab->getWysiwygConfig();

        return $config->getAllVariables();
    }

    /**
     * Open tab.
     *
     * @param string $tabName
     * @return PageForm
     */
    public function openTab($tabName)
    {
        $this->browser->find($this->header)->hover();
        $tab = $this->getContainerElement($tabName);
        $tabHeader = $tab->find('.//*[contains(@class,"admin__collapsible-title")]', Locator::SELECTOR_XPATH);
        if ($tabHeader->isVisible() && !strpos($tabHeader->getAttribute('class'), '_show')) {
            $tabHeader->hover();
            $tabHeader->click();
        };
        return $this;
    }
}
