<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable;

use Mtf\Block\Form;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

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
     * 'Show Sample block' button
     *
     * @var string
     */
    protected $showSample = '//dt[@id="dt-samples"]/a';

    /**
     * Sample title block
     *
     * @var string
     */
    protected $samplesTitle = '//input[@name="product[samples_title]"]';

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
     * @param Element $element
     * @return SampleRow
     */
    public function getRowBlock($index, Element $element = null)
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
     * @param Element $element
     * @return void
     */
    public function fillSamples(array $fields = null, Element $element = null)
    {
        $element = $element ?: $this->_rootElement;
        if (!$element->find($this->samplesTitle, Locator::SELECTOR_XPATH)->isVisible()) {
            $element->find($this->showSample, Locator::SELECTOR_XPATH)->click();
        }
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
     * @param Element|null $element
     * @return array
     */
    public function getDataSamples(array $fields = null, Element $element = null)
    {
        $element = $element ?: $this->_rootElement;
        if (!$element->find($this->samplesTitle, Locator::SELECTOR_XPATH)->isVisible()) {
            $element->find($this->showSample, Locator::SELECTOR_XPATH)->click();
        }
        $mapping = $this->dataMapping(['title' => $fields['title']]);
        $newFields = $this->_getData($mapping);
        foreach ($fields['downloadable']['sample'] as $index => $sample) {
            $newFields['downloadable']['sample'][$index] = $this->getRowBlock($index, $element)
                ->getDataSampleRow($sample);
        }
        return $newFields;
    }
}
