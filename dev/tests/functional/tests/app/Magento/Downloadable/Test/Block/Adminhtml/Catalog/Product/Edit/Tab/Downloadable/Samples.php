<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $element = $element ? : $this->_rootElement;
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
        $element = $element ? : $this->_rootElement;
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
        $element = $element ? : $this->_rootElement;
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
