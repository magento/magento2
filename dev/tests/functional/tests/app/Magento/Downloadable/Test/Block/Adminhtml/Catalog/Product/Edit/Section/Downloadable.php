<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Section;

use Magento\Mtf\Client\Element;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Ui\Test\Block\Adminhtml\Section;
use Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Section\Downloadable\Samples;
use Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Section\Downloadable\Links;

/**
 * Product downloadable section.
 */
class Downloadable extends Section
{
    /**
     * 'Add Link' button.
     *
     * @var string
     */
    protected $addNewRow = '[data-index="link"] [data-action="add_new_row"]';

    /**
     * Downloadable block.
     *
     * @var string
     */
    protected $downloadableBlock = '[data-index="container_%s"]';

    /**
     * Locator for is downloadable product checkbox.
     *
     * @var string
     */
    protected $isDownloadableProduct = '[name="is_downloadable"]';

    /**
     * Get Downloadable block.
     *
     * @param string $type
     * @param SimpleElement $element
     * @return Samples|Links
     */
    public function getDownloadableBlock($type, SimpleElement $element = null)
    {
        $element = $element ?: $this->_rootElement;
        return $this->blockFactory->create(
            'Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Section\Downloadable\\' . $type,
            ['element' => $element->find(sprintf($this->downloadableBlock, strtolower($type)), Locator::SELECTOR_CSS)]
        );
    }

    /**
     * Get data to fields on downloadable tab.
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
     * Fill downloadable information.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     */
    public function setFieldsData(array $fields, SimpleElement $element = null)
    {
        $context = $element ?: $this->_rootElement;
        $isDownloadable = $context->find($this->isDownloadableProduct);
        if ($isDownloadable->isVisible() && $isDownloadable->getAttribute('value') != '1') {
            $isDownloadable->click();
        }
        if (isset($fields['downloadable_sample']['value'])) {
            $this->getDownloadableBlock('Samples')->fillSamples($fields['downloadable_sample']['value']);
        }

        if (isset($fields['downloadable_links']['value'])) {
            $this->getDownloadableBlock('Links')->fillLinks($fields['downloadable_links']['value']);
        }

        return $this;
    }

    /**
     * Set "Is this downloadable Product?" value.
     *
     * @param string $downloadable
     * @param SimpleElement|null $element
     * @return void
     */
    public function setIsDownloadable(string $downloadable = 'Yes', SimpleElement $element = null): void
    {
        $context = $element ?: $this->_rootElement;
        $isDownloadable = $context->find($this->isDownloadableProduct);
        $value = 'Yes' == $downloadable ? '1' : '0';
        if ($isDownloadable->isVisible() && $isDownloadable->getAttribute('value') != $value) {
            $isDownloadable->click();
        }
    }
}
