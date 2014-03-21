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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog manage products block
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml;

class Product extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'catalog/product.phtml';

    /**
     * @var \Magento\Catalog\Model\Product\TypeFactory
     */
    protected $_typeFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\Product\TypeFactory $typeFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\Product\TypeFactory $typeFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = array()
    ) {
        $this->_productFactory = $productFactory;
        $this->_typeFactory = $typeFactory;

        parent::__construct($context, $data);
    }

    /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
        $addButtonProps = array(
            'id' => 'add_new_product',
            'label' => __('Add Product'),
            'class' => 'btn-add',
            'button_class' => 'btn-round',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->_getAddProductButtonOptions()
        );
        $this->_addButton('add_new', $addButtonProps);

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Magento\Catalog\Block\Adminhtml\Product\Grid', 'product.grid')
        );
        return parent::_prepareLayout();
    }

    /**
     * Retrieve options for 'Add Product' split button
     *
     * @return array
     */
    protected function _getAddProductButtonOptions()
    {
        $splitButtonOptions = array();

        foreach ($this->_typeFactory->create()->getOptionArray() as $key => $label) {
            $splitButtonOptions[$key] = array(
                'label' => $label,
                'onclick' => "setLocation('" . $this->_getProductCreateUrl($key) . "')",
                'default' => \Magento\Catalog\Model\Product\Type::DEFAULT_TYPE == $key
            );
        }

        return $splitButtonOptions;
    }

    /**
     * Retrieve product create url by specified product type
     *
     * @param string $type
     * @return string
     */
    protected function _getProductCreateUrl($type)
    {
        return $this->getUrl(
            'catalog/*/new',
            array('set' => $this->_productFactory->create()->getDefaultAttributeSetId(), 'type' => $type)
        );
    }

    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    /**
     * Check whether it is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }
}
