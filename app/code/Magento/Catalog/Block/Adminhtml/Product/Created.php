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
 * Product after creation popup window
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product;

class Created extends \Magento\Backend\Block\Widget
{
    protected $_configurableProduct;
    protected $_product;

    /**
     * @var string
     */
    protected $_template = 'catalog/product/created.phtml';

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = array()
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_productFactory = $productFactory;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        $this->addChild('close_button', 'Magento\Backend\Block\Widget\Button', array(
            'label'   => __('Close Window'),
            'onclick' => 'addProduct(true)'
        ));
    }


    public function getCloseButtonHtml()
    {
        return $this->getChildHtml('close_button');
    }

    public function getProductId()
    {
        return (int) $this->getRequest()->getParam('id');
    }

    /**
     * Identifies edit mode of popup
     *
     * @return boolean
     */
    public function isEdit()
    {
        return (bool)$this->getRequest()->getParam('edit');
    }

    /**
     * Retrieve serialized json with configurable attributes values of simple
     *
     * @return string
     */
    public function getAttributesJson()
    {
        $result = array();
        foreach ($this->getAttributes() as $attribute) {
            $value = $this->getProduct()->getAttributeText($attribute->getAttributeCode());

            $result[] = array(
                'label'         => $value,
                'value_index'   => $this->getProduct()->getData($attribute->getAttributeCode()),
                'attribute_id'  => $attribute->getId()
            );
        }

        return $this->_jsonEncoder->encode($result);
    }

    /**
     * Retrieve array of attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        $configurableProduct = $this->getConfigurableProduct();
        if ($configurableProduct->getId()) {
            return $configurableProduct
                ->getTypeInstance()
                ->getUsedProductAttributes($configurableProduct);
        }

        $attributes = array();
        $attributesIds = $this->getRequest()->getParam('required');
        if ($attributesIds) {
            $product = $this->getProduct();
            $typeInstance = $product->getTypeInstance();
            foreach (explode(',', $attributesIds) as $attributeId) {
                $attribute = $typeInstance->getAttributeById($attributeId, $product);
                if ($attribute) {
                    $attributes[] = $attribute;
                }
            }
        }
        return $attributes;
    }

    /**
     * Retrieve configurable product for created/edited simple
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getConfigurableProduct()
    {
        if ($this->_configurableProduct === null) {
            $this->_configurableProduct = $this->_productFactory->create()->setStore(0)
                ->load($this->getRequest()->getParam('product'));
        }
        return $this->_configurableProduct;
    }

    /**
     * Retrieve product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if ($this->_product === null) {
            $this->_product = $this->_productFactory->create()->setStore(0)
                ->load($this->getRequest()->getParam('id'));
        }
        return $this->_product;
    }
}
