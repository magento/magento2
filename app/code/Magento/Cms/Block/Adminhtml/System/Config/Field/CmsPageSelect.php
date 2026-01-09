<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Block\Adminhtml\System\Config\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * CMS Page select field with AJAX search capability for system configuration
 *
 * Provides a searchable dropdown for selecting CMS pages, improving UX when
 * there are many pages in the system.
 */
class CmsPageSelect extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Cms::system/config/cms-page-select.phtml';

    /**
     * @var AbstractElement|null
     */
    private ?AbstractElement $element = null;

    /**
     * @param Context $context
     * @param PageCollectionFactory $pageCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly PageCollectionFactory $pageCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Render element HTML
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $this->element = $element;
        return $this->_toHtml();
    }

    /**
     * Get element HTML ID
     *
     * @return string
     */
    public function getElementId(): string
    {
        return $this->element->getHtmlId();
    }

    /**
     * Get element name
     *
     * @return string
     */
    public function getElementName(): string
    {
        return $this->element->getName();
    }

    /**
     * Get element value
     *
     * @return string
     */
    public function getElementValue(): string
    {
        return (string)$this->element->getValue();
    }

    /**
     * Get current selection label
     *
     * @return string
     */
    public function getCurrentLabel(): string
    {
        $value = $this->getElementValue();
        $currentOption = $this->getCurrentValueOption($value);
        return $currentOption['label'] ?? (string)__('-- Please Select --');
    }

    /**
     * Get search URL
     *
     * @return string
     */
    public function getSearchUrl(): string
    {
        return $this->getUrl('cms/page/search');
    }

    /**
     * Check if element is disabled
     *
     * @return bool
     */
    public function getIsDisabled(): bool
    {
        return (bool)$this->element->getDisabled();
    }

    /**
     * Get current value option data
     *
     * @param string $pageIdentifier
     * @return array
     */
    private function getCurrentValueOption(string $pageIdentifier): array
    {
        if ($pageIdentifier === '') {
            return [];
        }

        $collection = $this->pageCollectionFactory->create();
        $collection->addFieldToFilter('identifier', $pageIdentifier);
        $collection->addFieldToSelect(['page_id', 'title', 'identifier']);
        $collection->setPageSize(1);

        $page = $collection->getFirstItem();
        if ($page->getId()) {
            return [
                'value' => $pageIdentifier,
                'label' => $page->getTitle() . ' (ID: ' . $page->getId() . ')'
            ];
        }

        return ['value' => $pageIdentifier, 'label' => $pageIdentifier];
    }
}
