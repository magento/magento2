<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Attribute;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;

class CreateOptions extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::products';

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param AttributeFactory $attributeFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        AttributeFactory $attributeFactory
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->attributeFactory = $attributeFactory;
        parent::__construct($context);
    }

    /**
     * Search for attributes by part of attribute's label in admin store
     *
     * @return void
     */
    public function execute()
    {
        $this->getResponse()->representJson($this->jsonHelper->jsonEncode($this->saveAttributeOptions()));
    }

    /**
     * Save attribute options just created by user
     *
     * @return array
     * @TODO Move this logic to configurable product type model
     *   when full set of operations for attribute options during
     *   product creation will be implemented: edit labels, remove, reorder.
     * Currently only addition of options to end and removal of just added option is supported.
     */
    protected function saveAttributeOptions()
    {
        $options = (array)$this->getRequest()->getParam('options');
        $savedOptions = [];
        foreach ($options as $option) {
            if (isset($option['label']) && isset($option['is_new'])) {
                $attribute = $this->attributeFactory->create();
                $attribute->load($option['attribute_id']);
                $optionsBefore = $attribute->getSource()->getAllOptions(false);
                $attribute->setOption(
                    [
                        'value' => ['option_0' => [$option['label']]],
                        'order' => ['option_0' => count($optionsBefore) + 1],
                    ]
                );
                $attribute->save();
                $attribute = $this->attributeFactory->create();
                $attribute->load($option['attribute_id']);
                $optionsAfter = $attribute->getSource()->getAllOptions(false);
                $newOption = array_pop($optionsAfter);
                $savedOptions[$option['id']] = $newOption['value'];
            }
        }
        return $savedOptions;
    }
}
