<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * Sort rows data
     *
     * @var array
     */
    protected $sortRowsData = [];

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

            if (isset($sample['sort_order'])) {
                $currentSortOrder = (int)$sample['sort_order'];
                unset($sample['sort_order']);
            } else {
                $currentSortOrder = 0;
            }
            $this->getRowBlock($index, $element)->fillSampleRow($sample);

            $this->sortSample($index, $currentSortOrder, $element);
        }
        $this->sortRowsData = [];
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
            unset($sample['sort_order']);
            $processedSample = $this->getRowBlock($index, $element)
                ->getDataSampleRow($sample);
            $processedSample['sort_order'] = $index;
            $newFields['downloadable']['sample'][$index] = $processedSample;
        }
        return $newFields;
    }

    /**
     * Sort sample element
     *
     * @param int $position
     * @param int $sortOrder
     * @param SimpleElement|null $element
     * @return void
     */
    protected function sortSample($position, $sortOrder, SimpleElement $element = null)
    {
        $currentSortRowData = ['current_position_in_grid' => $position, 'sort_order' => $sortOrder];
        foreach ($this->sortRowsData as &$sortRowData) {
            if ($sortRowData['sort_order'] > $currentSortRowData['sort_order']) {
                // need to reload block because we are changing dom
                $target = $this->getRowBlock($sortRowData['current_position_in_grid'], $element)->getSortHandle();
                $this->getRowBlock($currentSortRowData['current_position_in_grid'], $element)->dragAndDropTo($target);

                $currentSortRowData['current_position_in_grid']--;
                $sortRowData['current_position_in_grid']++;
            }
        }
        unset($sortRowData);
        $this->sortRowsData[] = $currentSortRowData;
    }
}
