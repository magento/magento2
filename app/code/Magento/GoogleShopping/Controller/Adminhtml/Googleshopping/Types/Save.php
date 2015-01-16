<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types;

class Save extends \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types
{
    /**
     * Save attribute set mapping
     *
     * @return void
     */
    public function execute()
    {
        /** @var $typeModel \Magento\GoogleShopping\Model\Type */
        $typeModel = $this->_objectManager->create('Magento\GoogleShopping\Model\Type');
        $id = $this->getRequest()->getParam('type_id');
        if (!is_null($id)) {
            $typeModel->load($id);
        }

        try {
            $typeModel->setCategory($this->getRequest()->getParam('category'));
            if ($typeModel->getId()) {
                $collection = $this->_objectManager->create(
                    'Magento\GoogleShopping\Model\Resource\Attribute\Collection'
                )->addTypeFilter(
                    $typeModel->getId()
                )->load();
                foreach ($collection as $attribute) {
                    $attribute->delete();
                }
            } else {
                $typeModel->setAttributeSetId(
                    $this->getRequest()->getParam('attribute_set_id')
                )->setTargetCountry(
                    $this->getRequest()->getParam('target_country')
                );
            }
            $typeModel->save();

            $attributes = $this->getRequest()->getParam('attributes');
            $requiredAttributes = $this->_objectManager->get(
                'Magento\GoogleShopping\Model\Config'
            )->getRequiredAttributes();
            if (is_array($attributes)) {
                $typeId = $typeModel->getId();
                foreach ($attributes as $attrInfo) {
                    if (isset($attrInfo['delete']) && $attrInfo['delete'] == 1) {
                        continue;
                    }
                    $this->_objectManager->create(
                        'Magento\GoogleShopping\Model\Attribute'
                    )->setAttributeId(
                        $attrInfo['attribute_id']
                    )->setGcontentAttribute(
                        $attrInfo['gcontent_attribute']
                    )->setTypeId(
                        $typeId
                    )->save();
                    unset($requiredAttributes[$attrInfo['gcontent_attribute']]);
                }
            }

            $this->messageManager->addSuccess(__('The attribute mapping has been saved.'));
            if (!empty($requiredAttributes)) {
                $this->messageManager->addSuccess(
                    $this->_objectManager->get('Magento\GoogleShopping\Helper\Category')->getMessage()
                );
            }
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->messageManager->addError(__("We can't save Attribute Set Mapping."));
        }
        $this->_redirect('adminhtml/*/index', ['store' => $this->_getStore()->getId()]);
    }
}
