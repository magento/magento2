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
namespace Magento\Catalog\Controller\Adminhtml\Product\Group;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::products');
    }

    /**
     * @return void
     */
    public function execute()
    {
        $model = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute\Group');

        $model->setAttributeGroupName(
            $this->getRequest()->getParam('attribute_group_name')
        )->setAttributeSetId(
            $this->getRequest()->getParam('attribute_set_id')
        );

        if ($model->itemExists()) {
            $this->messageManager->addError(__('A group with the same name already exists.'));
        } else {
            try {
                $model->save();
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong while saving this group.'));
            }
        }
    }
}
