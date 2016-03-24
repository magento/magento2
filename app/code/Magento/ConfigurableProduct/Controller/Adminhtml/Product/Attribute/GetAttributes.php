<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Attribute;

use Magento\Backend\App\Action;
use Magento\ConfigurableProduct\Model\AttributesListInterface;

class GetAttributes extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::attributes_attributes';

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param Action\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param AttributesListInterface $attributesList
     */
    public function __construct(
        Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        AttributesListInterface $attributesList
    ) {
        $this->storeManager = $storeManager;
        $this->jsonHelper = $jsonHelper;
        $this->attributesList = $attributesList;
        parent::__construct($context);
    }

    /**
     * Get attributes
     *
     * @return void
     */
    public function execute()
    {
        $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::ADMIN_CODE);
        $attributes = $this->attributesList->getAttributes($this->getRequest()->getParam('attributes'));
        $this->getResponse()->representJson($this->jsonHelper->jsonEncode($attributes));
    }
}
