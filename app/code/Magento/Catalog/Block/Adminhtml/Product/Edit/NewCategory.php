<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * New category creation form
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class NewCategory extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_categoryFactory = $categoryFactory;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->setUseContainer(true);
    }

    /**
     * Form preparation
     *
     * @return void
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(['data' => ['id' => 'new_category_form', 'class' => 'admin__scope-old']]);
        $form->setUseContainer($this->getUseContainer());

        $form->addField('new_category_messages', 'note', []);

        $fieldset = $form->addFieldset('new_category_form_fieldset', []);

        $fieldset->addField(
            'new_category_name',
            'text',
            [
                'label' => __('Category Name'),
                'title' => __('Category Name'),
                'required' => true,
                'name' => 'new_category_name'
            ]
        );

        $fieldset->addField(
            'new_category_parent',
            'select',
            [
                'label' => __('Parent Category'),
                'title' => __('Parent Category'),
                'required' => true,
                'options' => $this->_getParentCategoryOptions(),
                'class' => 'validate-parent-category',
                'name' => 'new_category_parent',
                // @codingStandardsIgnoreStart
                'note' => __(
                    'If there are no custom parent categories, please use the default parent category. ' .
                    'You can reassign the category at any time in ' .
                    '<a href="%1" target="_blank">Products > Categories</a>.',
                    $this->getUrl('catalog/category')
                )
                // @codingStandardsIgnoreEnd
            ]
        );

        $this->setForm($form);
    }

    /**
     * Get parent category options
     *
     * @return array
     */
    protected function _getParentCategoryOptions()
    {
        $items = $this->_categoryFactory->create()->getCollection()->addAttributeToSelect(
            'name'
        )->addAttributeToSort(
            'entity_id',
            'ASC'
        )->setPageSize(
            3
        )->load()->getItems();

        $result = [];
        if (count($items) === 2) {
            $item = array_pop($items);
            $result = [$item->getEntityId() => $item->getName()];
        }

        return $result;
    }

    /**
     * Category save action URL
     *
     * @return string
     */
    public function getSaveCategoryUrl()
    {
        return $this->getUrl('catalog/category/save');
    }

    /**
     * Attach new category dialog widget initialization
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        $widgetOptions = $this->_jsonEncoder->encode(
            [
                'suggestOptions' => [
                    'source' => $this->getUrl('catalog/category/suggestCategories'),
                    'valueField' => '#new_category_parent',
                    'className' => 'category-select',
                    'multiselect' => true,
                    'showAll' => true,
                ],
                'saveCategoryUrl' => $this->getUrl('catalog/category/save'),
            ]
        );
        //TODO: JavaScript logic should be moved to separate file or reviewed
        return <<<HTML
<script>
require(["jquery","mage/mage"],function($) {  // waiting for dependencies at first
    $(function(){ // waiting for page to load to have '#category_ids-template' available
        $('#new-category').mage('newCategoryDialog', $widgetOptions);
    });
});
</script>
HTML;
    }
}
