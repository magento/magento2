<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Attribute;

use Magento\Backend\App\Action;
use Magento\ConfigurableProduct\Model\SuggestedAttributeList;

class SuggestConfigurableAttributes extends Action
{
    /**
     * @var \Magento\ConfigurableProduct\Model\SuggestedAttributeList
     */
    protected $attributeList;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $coreHelper;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Action\Context $context
     * @param SuggestedAttributeList $attributeList
     * @param \Magento\Core\Helper\Data $coreHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Action\Context $context,
        SuggestedAttributeList $attributeList,
        \Magento\Core\Helper\Data $coreHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->attributeList = $attributeList;
        $this->coreHelper = $coreHelper;
        $this->storeManager = $storeManager;
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

        $this->getResponse()->representJson(
            $this->coreHelper->jsonEncode(
                $this->attributeList->getSuggestedAttributes($this->getRequest()->getParam('label_part'))
            )
        );
    }
}
