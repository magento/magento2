<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form;

use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\AuthorizationInterface;

/**
 * Product form category field helper
 */
class Category extends \Magento\Framework\Data\Form\Element\Multiselect
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * Backend data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendData;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param AuthorizationInterface $authorization
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        AuthorizationInterface $authorization,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_collectionFactory = $collectionFactory;
        $this->_backendData = $backendData;
        $this->authorization = $authorization;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->_layout = $layout;
        if (!$this->isAllowed()) {
            $this->setType('hidden');
            $this->addClass('hidden');
        }
    }

    /**
     * Get values for select
     *
     * @return array
     */
    public function getValues()
    {
        $collection = $this->_getCategoriesCollection();
        $values = $this->getValue();
        if (!is_array($values)) {
            $values = explode(',', $values);
        }
        $collection->addAttributeToSelect('name');
        $collection->addIdFilter($values);

        $options = [];

        foreach ($collection as $category) {
            $options[] = ['label' => $category->getName(), 'value' => $category->getId()];
        }
        return $options;
    }

    /**
     * Get categories collection
     *
     * @return Collection
     */
    protected function _getCategoriesCollection()
    {
        return $this->_collectionFactory->create();
    }

    /**
     * Attach category suggest widget initialization
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        if (!$this->isAllowed()) {
            return '';
        }
        $htmlId = $this->getHtmlId();
        $suggestPlaceholder = __('start typing to search category');
        $selectorOptions = $this->_jsonEncoder->encode($this->_getSelectorOptions());
        $newCategoryCaption = __('New Category');

        $button = $this->_layout->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
                [
                    'id' => 'add_category_button',
                    'label' => $newCategoryCaption,
                    'title' => $newCategoryCaption,
                    'onclick' => 'jQuery("#new-category").modal("openModal")',
                    'disabled' => $this->getDisabled(),
                ]
            );
        $return = <<<HTML
    <input id="{$htmlId}-suggest" placeholder="$suggestPlaceholder" />
    <script>
        require(["jquery", "mage/mage"], function($){
            $('#{$htmlId}-suggest').mage('treeSuggest', {$selectorOptions});
        });
    </script>
HTML;
        return $return . $button->toHtml();
    }

    /**
     * Get selector options
     *
     * @return array
     */
    protected function _getSelectorOptions()
    {
        return [
            'source' => $this->_backendData->getUrl('catalog/category/suggestCategories'),
            'valueField' => '#' . $this->getHtmlId(),
            'className' => 'category-select',
            'multiselect' => true,
            'showAll' => true
        ];
    }

    /**
     * Whether permission is granted
     *
     * @return bool
     */
    protected function isAllowed()
    {
        return $this->authorization->isAllowed('Magento_Catalog::categories');
    }
}
