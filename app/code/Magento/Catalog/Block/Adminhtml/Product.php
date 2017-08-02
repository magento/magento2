<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog manage products block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml;

/**
 * @api
 * @since 2.0.0
 */
class Product extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var \Magento\Catalog\Model\Product\TypeFactory
     * @since 2.0.0
     */
    protected $_typeFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     * @since 2.0.0
     */
    protected $_productFactory;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Catalog\Model\Product\TypeFactory $typeFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Catalog\Model\Product\TypeFactory $typeFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = []
    ) {
        $this->_productFactory = $productFactory;
        $this->_typeFactory = $typeFactory;

        parent::__construct($context, $data);
    }

    /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'add_new_product',
            'label' => __('Add Product'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => \Magento\Backend\Block\Widget\Button\SplitButton::class,
            'options' => $this->_getAddProductButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        return parent::_prepareLayout();
    }

    /**
     * Retrieve options for 'Add Product' split button
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getAddProductButtonOptions()
    {
        $splitButtonOptions = [];
        $types = $this->_typeFactory->create()->getTypes();
        uasort(
            $types,
            function ($elementOne, $elementTwo) {
                return ($elementOne['sort_order'] < $elementTwo['sort_order']) ? -1 : 1;
            }
        );

        foreach ($types as $typeId => $type) {
            $splitButtonOptions[$typeId] = [
                'label' => __($type['label']),
                'onclick' => "setLocation('" . $this->_getProductCreateUrl($typeId) . "')",
                'default' => \Magento\Catalog\Model\Product\Type::DEFAULT_TYPE == $typeId,
            ];
        }

        return $splitButtonOptions;
    }

    /**
     * Retrieve product create url by specified product type
     *
     * @param string $type
     * @return string
     * @since 2.0.0
     */
    protected function _getProductCreateUrl($type)
    {
        return $this->getUrl(
            'catalog/*/new',
            ['set' => $this->_productFactory->create()->getDefaultAttributeSetId(), 'type' => $type]
        );
    }

    /**
     * Check whether it is single store mode
     *
     * @return bool
     * @since 2.0.0
     */
    public function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }
}
