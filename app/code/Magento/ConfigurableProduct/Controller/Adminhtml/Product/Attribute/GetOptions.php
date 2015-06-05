<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Attribute;

use Magento\Backend\App\Action;

class GetOptions extends Action
{
    /**
     * @var \Magento\ConfigurableProduct\Model\SuggestedAttributeList
     */
    protected $attributeList;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    protected $attributeFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory

    ) {
        $this->jsonHelper = $jsonHelper;
        $this->storeManager = $storeManager;
        $this->attributeFactory = $attributeFactory;
        parent::__construct($context);
    }

    /**
     * ACL check
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::attributes_attributes');
    }

    /**
     * Search for attributes by part of attribute's label in admin store
     *
     * @return void
     */
    public function execute()
    {
        $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::ADMIN_CODE);
        $attributes = [];
        foreach ($this->getRequest()->getParam('attributes') as $attributeId) {
            $attribute = $this->attributeFactory->create()->load($attributeId);
            $attributes[] = [
                'id' => $attribute->getId(),
                'label' => $attribute->getFrontendLabel(),
                'code' => $attribute->getAttributeCode(),
                'options' => $attribute->getSourceModel() ? $attribute->getSource()->getAllOptions(false) : [],
            ];
        }

        $this->getResponse()->representJson($this->jsonHelper->jsonEncode($attributes));
    }
}
