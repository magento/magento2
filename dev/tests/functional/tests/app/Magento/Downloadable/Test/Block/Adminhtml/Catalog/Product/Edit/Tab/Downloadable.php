<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Tab;

use Magento\Mtf\Client\Element;
use Magento\Mtf\Client\Locator;
use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Class Downloadable
 *
 * Product downloadable tab
 */
class Downloadable extends Tab
{
    /**
     * 'Add New Row' button
     *
     * @var string
     */
    protected $addNewRow = '[data-action=add-link]';

    /**
     * Downloadable block
     *
     * @var string
     */
    protected $downloadableBlock = '[data-tab-type="tab_content_downloadableInfo"]';

    /**
     * Selector for content "Downloadable Information" tab.
     *
     * @var string
     */
    protected $downloadableTabContent = '#downloadable_items-content';

    /**
     * Selector for trigger show/hide "Downloadable Information" tab.
     *
     * @var string
     */
    protected $downloadableTabTrigger = '[data-tab=downloadable_items] [data-role=trigger]';

    /**
     * "Is Downloadable" checkbox.
     *
     * @var string
     */
    protected $isDownloadable = '#is-downloaodable';

    /**
     * Get Downloadable block
     *
     * @param string $type
     * @param SimpleElement $element
     * @return \Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\Samples |
     *         \Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\Links
     */
    public function getDownloadableBlock($type, SimpleElement $element = null)
    {
        $element = $element ?: $this->_rootElement;
        return $this->blockFactory->create(
            'Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\\' . $type,
            ['element' => $element->find($this->downloadableBlock, Locator::SELECTOR_CSS)]
        );
    }

    /**
     * Get data to fields on downloadable tab
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return array
     */
    public function getDataFormTab($fields = null, SimpleElement $element = null)
    {
        $newFields = [];
        if (isset($fields['downloadable_sample']['value'])) {
            $newFields['downloadable_sample'] = $this->getDownloadableBlock('Samples')->getDataSamples(
                $fields['downloadable_sample']['value']
            );
        }
        if (isset($fields['downloadable_links']['value'])) {
            $newFields['downloadable_links'] = $this->getDownloadableBlock('Links')->getDataLinks(
                $fields['downloadable_links']['value']
            );
        }

        return $newFields;
    }

    /**
     * Fill downloadable information
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return $this
     */
    public function fillFormTab(array $fields, SimpleElement $element = null)
    {
        if (isset($fields['downloadable_sample']['value'])) {
            $this->getDownloadableBlock('Samples')->fillSamples($fields['downloadable_sample']['value']);
        }

        if (isset($fields['downloadable_links']['value'])) {
            $this->getDownloadableBlock('Links')->fillLinks($fields['downloadable_links']['value']);
        }

        return $this;
    }

    /**
     * Show "Downloadable Information" tab content.
     *
     * @return void
     */
    public function showContent()
    {
        $content = $this->_rootElement->find($this->downloadableTabContent);
        if (!$content->isVisible()) {
            $this->_rootElement->find($this->downloadableTabTrigger)->click();
            $this->waitForElementVisible($this->downloadableTabTrigger);
        }
    }

    /**
     * Clear downloadable block data.
     *
     * @param string $block
     * @return $this
     */
    public function clearDownloadableData($block)
    {
        $this->showContent();
        if ($block) {
            /** @var SimpleElement $downloadableBlock */
            $downloadableBlock = $this->getDownloadableBlock($block);
            if (method_exists($downloadableBlock, 'clearDownloadableData')) {
                $downloadableBlock->clearDownloadableData();
            }
        }
        return $this;
    }

    /**
     * Set "Is this downloadable Product?" checkbox value.
     *
     * @param string $value
     * @return void
     */
    public function setIsDownloadable($value = 'Yes')
    {
        $this->showContent();
        $element = $this->_rootElement->find($this->isDownloadable, Locator::SELECTOR_CSS, 'checkbox');
        if ($element->isVisible()) {
            $element->setValue($value);
        }
    }
}
