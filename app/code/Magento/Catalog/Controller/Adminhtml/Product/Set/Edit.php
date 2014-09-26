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
namespace Magento\Catalog\Controller\Adminhtml\Product\Set;

class Edit extends \Magento\Catalog\Controller\Adminhtml\Product\Set
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_title->add(__('Product Templates'));

        $this->_setTypeId();
        $attributeSet = $this->_objectManager->create(
            'Magento\Eav\Model\Entity\Attribute\Set'
        )->load(
            $this->getRequest()->getParam('id')
        );

        if (!$attributeSet->getId()) {
            $this->_redirect('catalog/*/index');
            return;
        }

        $this->_title->add($attributeSet->getId() ? $attributeSet->getAttributeSetName() : __('New Set'));

        $this->_coreRegistry->register('current_attribute_set', $attributeSet);

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Catalog::catalog_attributes_sets');
        $this->_addBreadcrumb(__('Catalog'), __('Catalog'));
        $this->_addBreadcrumb(__('Manage Product Sets'), __('Manage Product Sets'));

        $this->_view->renderLayout();
    }
}
