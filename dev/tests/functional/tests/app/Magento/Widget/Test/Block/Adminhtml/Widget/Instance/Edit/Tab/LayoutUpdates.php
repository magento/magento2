<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\LayoutUpdatesType\LayoutForm;

/**
 * Layout Updates form
 */
class LayoutUpdates extends Tab
{
    /**
     * Backend abstract block
     *
     * @var string
     */
    protected $templateBlock = './ancestor::body';

    /**
     * Form selector
     *
     * @var string
     */
    protected $formSelector = './/div[contains(@id,"page_group_container_%d")]';

    /**
     * 'Add Option' button
     *
     * @var string
     */
    protected $addLayoutUpdates = 'button.action-add';

    /**
     * Fill Layout Updates form
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fillFormTab(array $fields, SimpleElement $element = null)
    {
        foreach ($fields['layout']['value'] as $key => $field) {
            $this->addLayoutUpdates();
            $path = 'Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\LayoutUpdatesType\\';
            $pageGroup = explode('/', $field['page_group']);
            /** @var LayoutForm $layoutForm */
            $layoutForm = $this->blockFactory->create(
                $path . str_replace(" ", "", $pageGroup[0]),
                [
                    'element' => $this->_rootElement->find(sprintf($this->formSelector, $key), Locator::SELECTOR_XPATH)
                ]
            );
            $layoutForm->fillForm($field);
        }
        return $this;
    }

    /**
     * Click Add Layout Updates button
     *
     * @return void
     */
    protected function addLayoutUpdates()
    {
        $this->_rootElement->find($this->addLayoutUpdates)->click();
    }

    /**
     * Get backend abstract block
     *
     * @return \Magento\Backend\Test\Block\Template
     */
    protected function getTemplateBlock()
    {
        return $this->blockFactory->create(
            'Magento\Backend\Test\Block\Template',
            ['element' => $this->_rootElement->find($this->templateBlock, Locator::SELECTOR_XPATH)]
        );
    }
}
