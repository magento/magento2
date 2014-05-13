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
namespace Magento\GoogleShopping\Block\Adminhtml\Types\Edit;

/**
 * Adminhtml Google Content types mapping form block
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\GoogleShopping\Helper\Category|null
     */
    protected $_googleShoppingCategory = null;

    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $_elementFactory;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $_formFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Config
     *
     * @var \Magento\GoogleShopping\Model\Config
     */
    protected $_config;

    /**
     * Product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * EAV attribute set collection factory
     *
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory
     */
    protected $_eavCollectionFactory;

    /**
     * Type collection factory
     *
     * @var \Magento\GoogleShopping\Model\Resource\Type\CollectionFactory
     */
    protected $_typeCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\GoogleShopping\Model\Resource\Type\CollectionFactory $typeCollectionFactory
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $eavCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\GoogleShopping\Model\Config $config
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param \Magento\GoogleShopping\Helper\Category $googleShoppingCategory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\GoogleShopping\Model\Resource\Type\CollectionFactory $typeCollectionFactory,
        \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $eavCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\GoogleShopping\Model\Config $config,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        \Magento\GoogleShopping\Helper\Category $googleShoppingCategory,
        array $data = array()
    ) {
        $this->_typeCollectionFactory = $typeCollectionFactory;
        $this->_eavCollectionFactory = $eavCollectionFactory;
        $this->_productFactory = $productFactory;
        $this->_config = $config;
        $this->_coreRegistry = $registry;
        $this->_googleShoppingCategory = $googleShoppingCategory;
        $this->_elementFactory = $elementFactory;
        $this->_formFactory = $formFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $itemType = $this->getItemType();

        $fieldset = $form->addFieldset('content_fieldset', array('legend' => __('Attribute set mapping')));

        if (!($targetCountry = $itemType->getTargetCountry())) {
            $isoKeys = array_keys($this->_getCountriesArray());
            $targetCountry = isset($isoKeys[0]) ? $isoKeys[0] : null;
        }
        $countrySelect = $fieldset->addField(
            'select_target_country',
            'select',
            array(
                'label' => __('Target Country'),
                'title' => __('Target Country'),
                'name' => 'target_country',
                'required' => true,
                'options' => $this->_getCountriesArray(),
                'value' => $targetCountry
            )
        );
        if ($itemType->getTargetCountry()) {
            $countrySelect->setDisabled(true);
        }

        $attributeSetsSelect = $this->getAttributeSetsSelectElement(
            $targetCountry
        )->setValue(
            $itemType->getAttributeSetId()
        );
        if ($itemType->getAttributeSetId()) {
            $attributeSetsSelect->setDisabled(true);
        }

        $fieldset->addField(
            'attribute_set',
            'note',
            array(
                'label' => __('Attribute Set'),
                'title' => __('Attribute Set'),
                'required' => true,
                'text' => '<div id="attribute_set_select">' . $attributeSetsSelect->toHtml() . '</div>'
            )
        );

        $categories = $this->_googleShoppingCategory->getCategories();
        $fieldset->addField(
            'category',
            'select',
            array(
                'label' => __('Google Product Category'),
                'title' => __('Google Product Category'),
                'required' => true,
                'name' => 'category',
                'options' => array_combine($categories, array_map('htmlspecialchars_decode', $categories)),
                'value' => $itemType->getCategory()
            )
        );

        $attributesBlock = $this->getLayout()->createBlock(
            'Magento\GoogleShopping\Block\Adminhtml\Types\Edit\Attributes'
        )->setTargetCountry(
            $targetCountry
        );
        if ($itemType->getId()) {
            $attributesBlock->setAttributeSetId($itemType->getAttributeSetId())->setAttributeSetSelected(true);
        }

        $attributes = $this->_coreRegistry->registry('attributes');
        if (is_array($attributes) && count($attributes) > 0) {
            $attributesBlock->setAttributesData($attributes);
        }

        $fieldset->addField(
            'attributes_box',
            'note',
            array(
                'label' => __('Attributes Mapping'),
                'text' => '<div id="attributes_details">' . $attributesBlock->toHtml() . '</div>'
            )
        );

        $form->addValues($itemType->getData());
        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setMethod('post');
        $form->setAction($this->getSaveUrl());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Get Select field with list of available attribute sets for some target country
     *
     * @param  string $targetCountry
     * @return \Magento\Framework\Data\Form\Element\Select
     */
    public function getAttributeSetsSelectElement($targetCountry)
    {
        $field = $this->_elementFactory->create('select');
        $field->setName(
            'attribute_set_id'
        )->setId(
            'select_attribute_set'
        )->setForm(
            $this->_formFactory->create()
        )->addClass(
            'required-entry'
        )->setValues(
            $this->_getAttributeSetsArray($targetCountry)
        );
        return $field;
    }

    /**
     * Get allowed country names array
     *
     * @return array
     */
    protected function _getCountriesArray()
    {
        $_allowed = $this->_config->getAllowedCountries();
        $result = array();
        foreach ($_allowed as $iso => $info) {
            $result[$iso] = $info['name'];
        }
        return $result;
    }

    /**
     * Get array with attribute setes which available for some target country
     *
     * @param  string $targetCountry
     * @return array
     */
    protected function _getAttributeSetsArray($targetCountry)
    {
        $entityType = $this->_productFactory->create()->getResource()->getEntityType();
        $collection = $this->_eavCollectionFactory->create()->setEntityTypeFilter($entityType->getId());

        $ids = array();
        $itemType = $this->getItemType();
        if (!($itemType instanceof \Magento\Framework\Object && $itemType->getId())) {
            $typesCollection = $this->_typeCollectionFactory->create()->addCountryFilter($targetCountry)->load();
            foreach ($typesCollection as $type) {
                $ids[] = $type->getAttributeSetId();
            }
        }

        $result = array('' => '');
        foreach ($collection as $attributeSet) {
            if (!in_array($attributeSet->getId(), $ids)) {
                $result[$attributeSet->getId()] = $attributeSet->getAttributeSetName();
            }
        }
        return $result;
    }

    /**
     * Get current attribute set mapping from register
     *
     * @return \Magento\GoogleShopping\Model\Type
     */
    public function getItemType()
    {
        return $this->_coreRegistry->registry('current_item_type');
    }

    /**
     * Get URL for saving the current map
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('adminhtml/*/save', array('type_id' => $this->getItemType()->getId()));
    }
}
