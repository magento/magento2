<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Group;

/**
 * Class \Magento\Catalog\Controller\Adminhtml\Product\Group\Save
 *
 * @since 2.0.0
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::products';

    /**
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $model = $this->_objectManager->create(\Magento\Eav\Model\Entity\Attribute\Group::class);

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
