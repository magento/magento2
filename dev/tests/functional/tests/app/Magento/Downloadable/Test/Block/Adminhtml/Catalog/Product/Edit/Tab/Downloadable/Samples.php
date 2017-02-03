<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Class SampleRow
 *
 * Sample Form of downloadable product
 */
class Samples extends Form
{
    /**
     * 'Add New Row for samples' button
     *
     * @var string
     */
    protected $addNewSampleRow = '//button[@id="add_sample_item"]';

    /**
     * Downloadable sample item block
     *
     * @var string
     */
    protected $rowBlock = '//*[@id="sample_items_body"]/tr[%d]';

    /**
     * Get Downloadable sample item block
     *
     * @param int $index
     * @param SimpleElement $element
     * @return SampleRow
     */
    public function getRowBlock($index, SimpleElement $element = null)
    {
        $element = $element ?: $this->_rootElement;
        return $this->blockFactory->create(
            'Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\SampleRow',
            ['element' => $element->find(sprintf($this->rowBlock, ++$index), Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Fill samples block
     *
     * @param array|null $fields
     * @param SimpleElement $element
     * @return void
     */
    public function fillSamples(array $fields = null, SimpleElement $element = null)
    {
        $element = $element ?: $this->_rootElement;
        $mapping = $this->dataMapping(['title' => $fields['title']]);
        $this->_fill($mapping);
        foreach ($fields['downloadable']['sample'] as $index => $sample) {
            $element->find($this->addNewSampleRow, Locator::SELECTOR_XPATH)->click();
            $this->getRowBlock($index, $element)->fillSampleRow($sample);
        }
    }

    /**
     * Get data samples block
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     */
    public function getDataSamples(array $fields = null, SimpleElement $element = null)
    {
        $element = $element ?: $this->_rootElement;
        $mapping = $this->dataMapping(['title' => $fields['title']]);
        $newFields = $this->_getData($mapping);
        foreach ($fields['downloadable']['sample'] as $index => $sample) {
            $newFields['downloadable']['sample'][$index] = $this->getRowBlock($index, $element)
                ->getDataSampleRow($sample);
        }
        return $newFields;
    }
}
