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
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product attributes tab
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Block\Catalog\Product\Edit\Tab;

class Attributes extends \Magento\Adminhtml\Block\Catalog\Form
{
    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Data\Form\Factory $formFactory
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Data\Form\Factory $formFactory,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\Registry $registry,
        array $data = array()
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_catalogData = $catalogData;
        parent::__construct($registry, $formFactory, $coreData, $context, $data);
    }

    /**
     * Load Wysiwyg on demand and prepare layout
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->_catalogData->isModuleEnabled('Magento_Cms')
            && $this->_wysiwygConfig->isEnabled()
        ) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    /**
     * Prepare attributes form
     *
     * @return null
     */
    protected function _prepareForm()
    {
        /** @var $group \Magento\Eav\Model\Entity\Attribute\Group */
        $group = $this->getGroup();
        if ($group) {
            /** @var \Magento\Data\Form $form */
            $form = $this->_formFactory->create();
            $product = $this->_coreRegistry->registry('product');
            $isWrapped = $this->_coreRegistry->registry('use_wrapper');
            if (!isset($isWrapped)) {
                $isWrapped = true;
            }
            $isCollapsable = $isWrapped && $group->getAttributeGroupCode() == 'product-details';
            $legend = $isWrapped ? __($group->getAttributeGroupName()) : null;
            // Initialize product object as form property to use it during elements generation
            $form->setDataObject($product);

            $fieldset = $form->addFieldset(
                'group-fields-' .$group->getAttributeGroupCode(),
                 array(
                    'class' => 'user-defined',
                    'legend' => $legend,
                    'collapsable' => $isCollapsable
                )
            );

            $attributes = $this->getGroupAttributes();

            $this->_setFieldset($attributes, $fieldset, array('gallery'));

            $urlKey = $form->getElement('url_key');
            if ($urlKey) {
                $urlKey->setRenderer(
                    $this->getLayout()->createBlock('Magento\Adminhtml\Block\Catalog\Form\Renderer\Attribute\Urlkey')
                );
            }

            $tierPrice = $form->getElement('tier_price');
            if ($tierPrice) {
                $tierPrice->setRenderer(
                    $this->getLayout()->createBlock('Magento\Adminhtml\Block\Catalog\Product\Edit\Tab\Price\Tier')
                );
            }

            $groupPrice = $form->getElement('group_price');
            if ($groupPrice) {
                $groupPrice->setRenderer(
                    $this->getLayout()->createBlock('Magento\Adminhtml\Block\Catalog\Product\Edit\Tab\Price\Group')
                );
            }

            $recurringProfile = $form->getElement('recurring_profile');
            if ($recurringProfile) {
                $recurringProfile->setRenderer(
                    $this->getLayout()->createBlock('Magento\Adminhtml\Block\Catalog\Product\Edit\Tab\Price\Recurring')
                );
            }

            // Add new attribute controls if it is not an image tab
            if (!$form->getElement('media_gallery')
                && $this->_authorization->isAllowed('Magento_Catalog::attributes_attributes')
                && $isWrapped
            ) {
                $attributeCreate = $this->getLayout()
                    ->createBlock('Magento\Adminhtml\Block\Catalog\Product\Edit\Tab\Attributes\Create');

                $attributeCreate->getConfig()
                    ->setAttributeGroupCode($group->getAttributeGroupCode())
                    ->setTabId('group_' . $group->getId())
                    ->setGroupId($group->getId())
                    ->setStoreId($form->getDataObject()->getStoreId())
                    ->setAttributeSetId($form->getDataObject()->getAttributeSetId())
                    ->setTypeId($form->getDataObject()->getTypeId())
                    ->setProductId($form->getDataObject()->getId());

                $attributeSearch = $this->getLayout()
                    ->createBlock('Magento\Adminhtml\Block\Catalog\Product\Edit\Tab\Attributes\Search')
                    ->setGroupId($group->getId())
                    ->setGroupCode($group->getAttributeGroupCode());

                $attributeSearch->setAttributeCreate($attributeCreate->toHtml());

                $fieldset->setHeaderBar($attributeSearch->toHtml());
            }

            $values = $product->getData();

            // Set default attribute values for new product or on attribute set change
            if (!$product->getId() || $product->dataHasChangedFor('attribute_set_id')) {
                foreach ($attributes as $attribute) {
                    if (!isset($values[$attribute->getAttributeCode()])) {
                        $values[$attribute->getAttributeCode()] = $attribute->getDefaultValue();
                    }
                }
            }

            if ($product->hasLockedAttributes()) {
                foreach ($product->getLockedAttributes() as $attribute) {
                    $element = $form->getElement($attribute);
                    if ($element) {
                        $element->setReadonly(true, true);
                    }
                }
            }
            $form->addValues($values);
            $form->setFieldNameSuffix('product');

            $this->_eventManager->dispatch('adminhtml_catalog_product_edit_prepare_form', array('form' => $form));

            $this->setForm($form);
        }
    }

    /**
     * Retrieve additional element types
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        $result = array(
            'price'    => 'Magento\Adminhtml\Block\Catalog\Product\Helper\Form\Price',
            'weight'   => 'Magento\Adminhtml\Block\Catalog\Product\Helper\Form\Weight',
            'gallery'  => 'Magento\Adminhtml\Block\Catalog\Product\Helper\Form\Gallery',
            'image'    => 'Magento\Adminhtml\Block\Catalog\Product\Helper\Form\Image',
            'boolean'  => 'Magento\Adminhtml\Block\Catalog\Product\Helper\Form\Boolean',
            'textarea' => 'Magento\Adminhtml\Block\Catalog\Helper\Form\Wysiwyg',
        );

        $response = new \Magento\Object();
        $response->setTypes(array());
        $this->_eventManager->dispatch('adminhtml_catalog_product_edit_element_types', array('response' => $response));

        foreach ($response->getTypes() as $typeName => $typeClass) {
            $result[$typeName] = $typeClass;
        }

        return $result;
    }
}
