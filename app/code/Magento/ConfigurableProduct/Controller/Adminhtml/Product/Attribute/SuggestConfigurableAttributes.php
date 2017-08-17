<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Attribute;

use Magento\Backend\App\Action;
use Magento\ConfigurableProduct\Model\SuggestedAttributeList;

/**
 * Class \Magento\ConfigurableProduct\Controller\Adminhtml\Product\Attribute\SuggestConfigurableAttributes
 *
 */
class SuggestConfigurableAttributes extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::products';

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

    /**
     * @param Action\Context $context
     * @param SuggestedAttributeList $attributeList
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Action\Context $context,
        SuggestedAttributeList $attributeList,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->attributeList = $attributeList;
        $this->jsonHelper = $jsonHelper;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Search for attributes by part of attribute's label in admin store
     *
     * @return void
     */
    public function execute()
    {
        $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::ADMIN_CODE);

        $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode(
                $this->attributeList->getSuggestedAttributes($this->getRequest()->getParam('label_part'))
            )
        );
    }
}
