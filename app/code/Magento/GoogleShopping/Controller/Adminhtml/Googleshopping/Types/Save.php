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
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $this->messageManager->addError(__("We can't save Attribute Set Mapping."));
        }
        $this->_redirect('adminhtml/*/index', array('store' => $this->_getStore()->getId()));
    }
}
