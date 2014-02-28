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
 * @package     Magento_Weee
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Weee\Model;

class Observer extends \Magento\Core\Model\AbstractModel
{
    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_productType;

    /**
     * Weee data
     *
     * @var \Magento\Weee\Helper\Data
     */
    protected $_weeeData = null;

    /**
     * @var Tax
     */
    protected $_weeeTax;

    /**
     * @var \Magento\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $productTypeConfig;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\View\LayoutInterface $layout
     * @param Tax $weeeTax
     * @param \Magento\Weee\Helper\Data $weeeData
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\View\LayoutInterface $layout,
        Tax $weeeTax,
        \Magento\Weee\Helper\Data $weeeData,
        \Magento\Catalog\Model\Product\Type $productType,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_layout = $layout;
        $this->_weeeTax = $weeeTax;
        $this->_productType = $productType;
        $this->_weeeData = $weeeData;
        $this->productTypeConfig = $productTypeConfig;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Assign custom renderer for product create/edit form weee attribute element
     *
     * @param \Magento\Event\Observer $observer
     * @return $this
     */
    public function setWeeeRendererInForm(\Magento\Event\Observer $observer)
    {
        //adminhtml_catalog_product_edit_prepare_form

        $form = $observer->getEvent()->getForm();

        $attributes = $this->_weeeTax->getWeeeAttributeCodes(true);
        foreach ($attributes as $code) {
            if ($weeeTax = $form->getElement($code)) {
                $weeeTax->setRenderer(
                    $this->_layout->createBlock('Magento\Weee\Block\Renderer\Weee\Tax')
                );
            }
        }

        return $this;
    }

    /**
     * Exclude WEEE attributes from standard form generation
     *
     * @param \Magento\Event\Observer $observer
     * @return $this
     */
    public function updateExcludedFieldList(\Magento\Event\Observer $observer)
    {
        //adminhtml_catalog_product_form_prepare_excluded_field_list

        $block      = $observer->getEvent()->getObject();
        $list       = $block->getFormExcludedFieldList();
        $attributes = $this->_weeeTax->getWeeeAttributeCodes(true);
        $list       = array_merge($list, array_values($attributes));

        $block->setFormExcludedFieldList($list);

        return $this;
    }

    /**
     * Get empty select object
     *
     * @return \Magento\DB\Select
     */
    protected function _getSelect()
    {
        return $this->_weeeTax->getResource()->getReadConnection()->select();
    }

    /**
     * Add new attribute type to manage attributes interface
     *
     * @param   \Magento\Event\Observer $observer
     * @return  $this
     */
    public function addWeeeTaxAttributeType(\Magento\Event\Observer $observer)
    {
        // adminhtml_product_attribute_types

        $response = $observer->getEvent()->getResponse();
        $types = $response->getTypes();
        $types[] = array(
            'value' => 'weee',
            'label' => __('Fixed Product Tax'),
            'hide_fields' => array(
                'is_unique',
                'is_required',
                'frontend_class',
                '_scope',
                '_default_value',
                '_front_fieldset',
            ),
        );

        $response->setTypes($types);

        return $this;
    }

    /**
     * Automaticaly assign backend model to weee attributes
     *
     * @param   \Magento\Event\Observer $observer
     * @return  $this
     */
    public function assignBackendModelToAttribute(\Magento\Event\Observer $observer)
    {
        $backendModel = \Magento\Weee\Model\Attribute\Backend\Weee\Tax::getBackendModelName();
        /** @var $object \Magento\Eav\Model\Entity\Attribute\AbstractAttribute */
        $object = $observer->getEvent()->getAttribute();
        if ($object->getFrontendInput() == 'weee') {
            $object->setBackendModel($backendModel);
            if (!$object->getApplyTo()) {
                $applyTo = array();
                foreach ($this->_productType->getOptions() as $option) {
                    if ($this->productTypeConfig->isProductSet($option['value'])) {
                        continue;
                    }
                    $applyTo[] = $option['value'];
                }
                $object->setApplyTo($applyTo);
            }
        }

        return $this;
    }

    /**
     * Add custom element type for attributes form
     *
     * @param \Magento\Event\Observer $observer
     * @return $this
     */
    public function updateElementTypes(\Magento\Event\Observer $observer)
    {
        $response = $observer->getEvent()->getResponse();
        $types    = $response->getTypes();
        $types['weee'] = 'Magento\Weee\Block\Element\Weee\Tax';
        $response->setTypes($types);
        return $this;
    }

    /**
     * Update WEEE amounts discount percents
     *
     * @param   \Magento\Event\Observer $observer
     * @return  $this
     */
    public function updateDiscountPercents(\Magento\Event\Observer $observer)
    {
        if (!$this->_weeeData->isEnabled()) {
            return $this;
        }

        $productCondition = $observer->getEvent()->getProductCondition();
        if ($productCondition) {
            $eventProduct = $productCondition;
        } else {
            $eventProduct = $observer->getEvent()->getProduct();
        }
        $this->_weeeTax->updateProductsDiscountPercent($eventProduct);

        return $this;
    }

    /**
     * Update options of the product view page
     *
     * @param   \Magento\Event\Observer $observer
     * @return  $this
     */
    public function updateProductOptions(\Magento\Event\Observer $observer)
    {
        /* @var $helper \Magento\Weee\Helper\Data */
        $helper = $this->_weeeData;
        if (!$helper->isEnabled()) {
            return $this;
        }

        $response = $observer->getEvent()->getResponseObject();
        $options  = $response->getAdditionalOptions();

        $_product = $this->_coreRegistry->registry('current_product');
        if (!$_product) {
            return $this;
        }

        $options['oldPlusDisposition'] = $helper->getOriginalAmount($_product);
        $options['plusDisposition'] = $helper->getAmount($_product);

        // Exclude Weee amount from excluding tax amount
        if (!$helper->typeOfDisplay($_product, array(
            Tax::DISPLAY_INCL, Tax::DISPLAY_INCL_DESCR,
        ))) {
            $options['exclDisposition'] = true;
        }

        $response->setAdditionalOptions($options);

        return $this;
    }

    /**
     * Process bundle options selection for prepare view json
     *
     * @param   \Magento\Event\Observer $observer
     * @return  $this
     */
    public function updateBundleProductOptions(\Magento\Event\Observer $observer)
    {
        /* @var $weeeHelper \Magento\Weee\Helper\Data */
        $weeeHelper = $this->_weeeData;
        if (!$weeeHelper->isEnabled()) {
            return $this;
        }

        $response = $observer->getEvent()->getResponseObject();
        $selection = $observer->getEvent()->getSelection();
        $options = $response->getAdditionalOptions();

        $_product = $this->_coreRegistry->registry('current_product');

        $typeDynamic = \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes\Extend::DYNAMIC;
        if (!$_product || $_product->getPriceType() != $typeDynamic) {
            return $this;
        }

        $amount          = $weeeHelper->getAmount($selection);
        $attributes      = $weeeHelper->getProductWeeeAttributes($_product, null, null, null, $weeeHelper->isTaxable());
        $amountInclTaxes = $weeeHelper->getAmountInclTaxes($attributes);
        $taxes           = $amountInclTaxes - $amount;
        $options['plusDisposition']    = $amount;
        $options['plusDispositionTax'] = ($taxes < 0) ? 0 : $taxes;
        // Exclude Weee amount from excluding tax amount
        if (!$weeeHelper->typeOfDisplay($_product, array(0, 1, 4))) {
            $options['exclDisposition'] = true;
        }

        $response->setAdditionalOptions($options);

        return $this;
    }
}
