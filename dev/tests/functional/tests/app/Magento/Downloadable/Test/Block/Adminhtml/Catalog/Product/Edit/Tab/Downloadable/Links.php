<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Class Links
 *
 * Link Form of downloadable product
 */
class Links extends Form
{
    /**
     * 'Add New Row for links' button
     *
     * @var string
     */
    protected $addNewLinkRow = '//button[@id="add_link_item"]';

    /**
     * Downloadable link item block
     *
     * @var string
     */
    protected $rowBlock = '//*[@id="link_items_body"]/tr[%d]';

    /**
     * Downloadable link title block
     *
     * @var string
     */
    protected $title = "//*[@id='downloadable_links_title']";

    /**
     * Add new link row button block
     *
     * @var string
     */
    protected $addLinkButtonBlock = '[data-ui-id=downloadable-links] .col-actions-add:last-child';

    /**
     * Get Downloadable link item block
     *
     * @param int $index
     * @param SimpleElement $element
     * @return LinkRow
     */
    public function getRowBlock($index, SimpleElement $element = null)
    {
        $element = $element ?: $this->_rootElement;
        return $this->blockFactory->create(
            'Magento\Downloadable\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable\LinkRow',
            ['element' => $element->find(sprintf($this->rowBlock, ++$index), Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Fill links block
     *
     * @param array $fields
     * @param SimpleElement $element
     * @return void
     */
    public function fillLinks(array $fields, SimpleElement $element = null)
    {
        $element = $element ?: $this->_rootElement;
        $mapping = $this->dataMapping(
            ['title' => $fields['title'], 'links_purchased_separately' => $fields['links_purchased_separately']]
        );
        $this->_fill($mapping);
        foreach ($fields['downloadable']['link'] as $index => $link) {
            $rowBlock = $this->getRowBlock($index, $element);
            if (!$rowBlock->isVisible()) {
                $element->find($this->addNewLinkRow, Locator::SELECTOR_XPATH)->click();
            }
            $rowBlock->fillLinkRow($link);
        }
    }

    /**
     * Get data links block
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     */
    public function getDataLinks(array $fields = null, SimpleElement $element = null)
    {
        $element = $element ?: $this->_rootElement;
        $mapping = $this->dataMapping(
            ['title' => $fields['title'], 'links_purchased_separately' => $fields['links_purchased_separately']]
        );
        $newFields = $this->_getData($mapping);
        foreach ($fields['downloadable']['link'] as $index => $link) {
            $newFields['downloadable']['link'][$index] = $this->getRowBlock($index, $element)
                ->getDataLinkRow($link);
        }
        return $newFields;
    }

    /**
     * Delete all links and clear title.
     *
     * @return void
     */
    public function clearDownloadableData()
    {
        $this->_rootElement->find($this->title, Locator::SELECTOR_XPATH)->setValue('');
        $index = 1;
        while ($this->_rootElement->find(sprintf($this->rowBlock, $index), Locator::SELECTOR_XPATH)->isVisible()) {
            $rowBlock = $this->getRowBlock($index - 1);
            $rowBlock->clickDeleteButton();
            ++$index;
        }
    }
}
