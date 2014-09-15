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
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Action\Context $context
     * @param SuggestedAttributeList $attributeList
     * @param \Magento\Core\Helper\Data $coreHelper
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(
        Action\Context $context,
        SuggestedAttributeList $attributeList,
        \Magento\Core\Helper\Data $coreHelper,
        \Magento\Framework\StoreManagerInterface $storeManager
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
