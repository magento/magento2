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
 * New product attribute created on product edit page
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Attribute\NewAttribute\Product;

class Created extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'catalog/product/attribute/new/created.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $_setFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Framework\Registry $registry,
        array $data = array()
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_setFactory = $setFactory;
        $this->_attributeFactory = $attributeFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve list of product attributes
     *
     * @return array
     */
    protected function _getGroupAttributes()
    {
        $attributes = array();
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $this->_coreRegistry->registry('product');
        foreach ($product->getAttributes($this->getRequest()->getParam('group')) as $attribute) {
            /** @var $attribute \Magento\Eav\Model\Entity\Attribute */
            if ($attribute->getId() == $this->getRequest()->getParam('attribute')) {
                $attributes[] = $attribute;
            }
        }
        return $attributes;
    }

    /**
     * Retrieve HTML for 'Close' button
     *
     * @return string
     */
    public function getCloseButtonHtml()
    {
        return $this->getChildHtml('close_button');
    }

    /**
     * Retrieve attributes data as JSON
     *
     * @return string
     */
    public function getAttributesBlockJson()
    {
        $result = array();
        if ($this->getRequest()->getParam('product_tab') == 'variations') {
            /** @var $attribute \Magento\Eav\Model\Entity\Attribute */
            $attribute = $this->_attributeFactory->create()->load($this->getRequest()->getParam('attribute'));
            $result = array(
                'tab' => $this->getRequest()->getParam('product_tab'),
                'attribute' => array(
                    'id' => $attribute->getId(),
                    'label' => $attribute->getFrontendLabel(),
                    'code' => $attribute->getAttributeCode(),
                    'options' => $attribute->getSourceModel() ? $attribute->getSource()->getAllOptions(false) : array()
                )
            );
        }
        $newAttributeSetId = $this->getRequest()->getParam('new_attribute_set_id');
        if ($newAttributeSetId) {
            /** @var $attributeSet \Magento\Eav\Model\Entity\Attribute\Set */
            $attributeSet = $this->_setFactory->create()->load($newAttributeSetId);
            $result['set'] = array('id' => $attributeSet->getId(), 'label' => $attributeSet->getAttributeSetName());
        }

        return $this->_jsonEncoder->encode($result);
    }
}
