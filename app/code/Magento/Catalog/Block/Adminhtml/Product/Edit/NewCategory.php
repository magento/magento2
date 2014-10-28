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
        array $data = array()
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
        $form = $this->_formFactory->create(array('data' => array('id' => 'new_category_form')));
        $form->setUseContainer($this->getUseContainer());

        $form->addField('new_category_messages', 'note', array());

        $fieldset = $form->addFieldset('new_category_form_fieldset', array());

        $fieldset->addField(
            'new_category_name',
            'text',
            array(
                'label' => __('Category Name'),
                'title' => __('Category Name'),
                'required' => true,
                'name' => 'new_category_name'
            )
        );

        $fieldset->addField(
            'new_category_parent',
            'select',
            array(
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
            )
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

        $result = array();
        if (count($items) === 2) {
            $item = array_pop($items);
            $result = array($item->getEntityId() => $item->getName());
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
            array(
                'suggestOptions' => array(
                    'source' => $this->getUrl('catalog/category/suggestCategories'),
                    'valueField' => '#new_category_parent',
                    'className' => 'category-select',
                    'multiselect' => true,
                    'showAll' => true
                ),
                'saveCategoryUrl' => $this->getUrl('catalog/category/save')
            )
        );
        //TODO: JavaScript logic should be moved to separate file or reviewed
        return <<<HTML
<script type="text/javascript">
require(["jquery","mage/mage"],function($) {  // waiting for dependencies at first
    $(function(){ // waiting for page to load to have '#category_ids-template' available
        $('#new-category').mage('newCategoryDialog', $widgetOptions);
    });
});
</script>
HTML;
    }
}
