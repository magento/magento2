<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * Selector for trigger show/hide "Downloadable Information" section.
     *
     * @var string
     */
    protected $sectionTrigger = '[data-tab=downloadable_items] [data-role=trigger]';

    /**
     * Selector for "Is Downloadable product" checkbox.
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
    public function getFieldsData($fields = null, SimpleElement $element = null)
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
    public function setFieldsData(array $fields, SimpleElement $element = null)
    {
        $this->showContent();
        if (isset($fields['downloadable_sample']['value'])) {
            $this->getDownloadableBlock('Samples')->fillSamples($fields['downloadable_sample']['value']);
        }

        if (isset($fields['downloadable_links']['value'])) {
            $this->getDownloadableBlock('Links')->fillLinks($fields['downloadable_links']['value']);
        }

        return $this;
    }

    /**
     * Show "Downloadable" section content.
     *
     * @return void
     */
    private function showContent()
    {
        $isDownloadable = $this->_rootElement->find($this->isDownloadable);
        if (!$isDownloadable->isVisible()) {
            //Open Section
            $this->_rootElement->find($this->sectionTrigger)->click();
            $this->waitForElementVisible($this->isDownloadable);
            //Select "Is Downloadable" checkbox
            if (!$isDownloadable->isSelected()) {
                $isDownloadable->click();
            }
        }
    }
}
