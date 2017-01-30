<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\WidgetInstanceType;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Responds for filling layout form.
 */
class WidgetInstanceForm extends Form
{
    /**
     * Widget option chooser button.
     *
     * @var string
     */
    protected $chooser = '//*[@class="chooser_container"]//a/img[contains(@alt,"Open Chooser")]';

    /**
     * Widget option apply button.
     *
     * @var string
     */
    protected $apply = '//*[@class="chooser_container"]//a/img[contains(@alt,"Apply")]';

    /**
     * Backend abstract block.
     *
     * @var string
     */
    protected $templateBlock = './ancestor::body';

    /**
     * Selector for action header.
     *
     * @var string
     */
    protected $header = '.page-header';

    /**
     * Selector for footer.
     *
     * @var string
     */
    protected $footer = '.page-footer';

    /**
     * Filling layout form.
     *
     * @param array $layoutFields
     * @param SimpleElement $element
     * @return void
     */
    public function fillForm(array $layoutFields, SimpleElement $element = null)
    {
        $element = $element === null ? $this->_rootElement : $element;
        $mapping = $this->dataMapping($layoutFields);
        foreach ($mapping as $key => $values) {
            $this->_fill([$key => $values], $element);
            $this->getTemplateBlock()->waitLoader();
        }
    }

    /**
     * Getting options data form on the product form.
     *
     * @param array $fields
     * @param SimpleElement $element
     * @return array
     */
    public function getDataOptions(array $fields = null, SimpleElement $element = null)
    {
        $element = $element === null ? $this->_rootElement : $element;
        $mapping = $this->dataMapping($fields);
        return $this->_getData($mapping, $element);
    }

    /**
     * Get backend abstract block.
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

    /**
     * Click element on the page
     *
     * @param string $anchor
     * @param string $element
     * @param string $anchorStrategy [optional]
     * @param string $elementStrategy [optional]
     * @return bool
     */
    protected function clickOnElement(
        $anchor,
        $element,
        $anchorStrategy = Locator::SELECTOR_CSS,
        $elementStrategy = Locator::SELECTOR_CSS
    ) {
        try {
            $this->browser->find($anchor, $anchorStrategy)->hover();
            $this->_rootElement->find($element, $elementStrategy)->click();
        } catch (\PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
            return false;
        }
        return true;
    }
}
