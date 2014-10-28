<?php
/**
 *
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
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\GenerateVariations;

use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Catalog\Model\Resource\Eav\AttributeFactory;

class Index extends Action
{
    /**
     * @var Product\Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @var Product\Builder
     */
    protected $productBuilder;

    /**
     * @var \Magento\Catalog\Model\Resource\Eav\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @param Action\Context $context
     * @param Product\Initialization\Helper $initializationHelper
     * @param Product\Builder $productBuilder
     * @param AttributeFactory $attributeFactory
     */
    public function __construct(
        Action\Context $context,
        Product\Initialization\Helper $initializationHelper,
        Product\Builder $productBuilder,
        AttributeFactory $attributeFactory
    ) {
        $this->initializationHelper = $initializationHelper;
        $this->productBuilder = $productBuilder;
        $this->attributeFactory = $attributeFactory;
        parent::__construct($context);
    }

    /**
     * Check for is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::products');
    }

    /**
     * Save attribute options just created by user
     *
     * @return void
     * @TODO Move this logic to configurable product type model
     *   when full set of operations for attribute options during
     *   product creation will be implemented: edit labels, remove, reorder.
     * Currently only addition of options to end and removal of just added option is supported.
     */
    protected function _saveAttributeOptions()
    {
        $productData = (array)$this->getRequest()->getParam('product');
        if (!isset($productData['configurable_attributes_data'])) {
            return;
        }

        foreach ($productData['configurable_attributes_data'] as &$attributeData) {
            $values = array();
            foreach ($attributeData['values'] as $valueId => $priceData) {
                if (isset($priceData['label'])) {
                    $attribute = $this->attributeFactory->create();
                    $attribute->load($attributeData['attribute_id']);
                    $optionsBefore = $attribute->getSource()->getAllOptions(false);

                    $attribute->setOption(
                        array(
                            'value' => array('option_0' => array($priceData['label'])),
                            'order' => array('option_0' => count($optionsBefore) + 1)
                        )
                    );
                    $attribute->save();

                    $attribute = $this->attributeFactory->create();
                    $attribute->load($attributeData['attribute_id']);
                    $optionsAfter = $attribute->getSource()->getAllOptions(false);

                    $newOption = array_pop($optionsAfter);

                    unset($priceData['label']);
                    $valueId = $newOption['value'];
                    $priceData['value_index'] = $valueId;
                }
                $values[$valueId] = $priceData;
            }
            $attributeData['values'] = $values;
        }

        $this->getRequest()->setParam('product', $productData);
    }

    /**
     * Generate product variations matrix
     *
     * @return void
     */
    public function execute()
    {
        $this->_saveAttributeOptions();
        $this->getRequest()->setParam('variations-matrix', array());
        $this->initializationHelper->initialize($this->productBuilder->build($this->getRequest()));
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
