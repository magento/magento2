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

class Save extends \Magento\Catalog\Controller\Adminhtml\Product\Set
{
    /**
     * Retrieve catalog product entity type id
     *
     * @return int
     */
    protected function _getEntityTypeId()
    {
        if (is_null($this->_coreRegistry->registry('entityType'))) {
            $this->_setTypeId();
        }
        return $this->_coreRegistry->registry('entityType');
    }

    /**
     * Save attribute set action
     *
     * [POST] Create attribute set from another set and redirect to edit page
     * [AJAX] Save attribute set data
     *
     * @return void
     */
    public function execute()
    {
        $entityTypeId = $this->_getEntityTypeId();
        $hasError = false;
        $attributeSetId = $this->getRequest()->getParam('id', false);
        $isNewSet = $this->getRequest()->getParam('gotoEdit', false) == '1';

        /* @var $model \Magento\Eav\Model\Entity\Attribute\Set */
        $model = $this->_objectManager->create(
            'Magento\Eav\Model\Entity\Attribute\Set'
        )->setEntityTypeId(
            $entityTypeId
        );

        /** @var $filterManager \Magento\Framework\Filter\FilterManager */
        $filterManager = $this->_objectManager->get('Magento\Framework\Filter\FilterManager');

        try {
            if ($isNewSet) {
                //filter html tags
                $name = $filterManager->stripTags($this->getRequest()->getParam('attribute_set_name'));
                $model->setAttributeSetName(trim($name));
            } else {
                if ($attributeSetId) {
                    $model->load($attributeSetId);
                }
                if (!$model->getId()) {
                    throw new \Magento\Framework\Model\Exception(__('This attribute set no longer exists.'));
                }
                $data = $this->_objectManager->get(
                    'Magento\Core\Helper\Data'
                )->jsonDecode(
                    $this->getRequest()->getPost('data')
                );

                //filter html tags
                $data['attribute_set_name'] = $filterManager->stripTags($data['attribute_set_name']);

                $model->organizeData($data);
            }

            $model->validate();
            if ($isNewSet) {
                $model->save();
                $model->initFromSkeleton($this->getRequest()->getParam('skeleton_set'));
            }
            $model->save();
            $this->messageManager->addSuccess(__('You saved the attribute set.'));
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $hasError = true;
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('An error occurred while saving the attribute set.'));
            $hasError = true;
        }

        if ($isNewSet) {
            if ($this->getRequest()->getPost('return_session_messages_only')) {
                /** @var $block \Magento\Framework\View\Element\Messages */
                $block = $this->_objectManager->get('Magento\Framework\View\Element\Messages');
                $block->setMessages($this->messageManager->getMessages(true));
                $body = $this->_objectManager->get(
                    'Magento\Core\Helper\Data'
                )->jsonEncode(
                    array('messages' => $block->getGroupedHtml(), 'error' => $hasError, 'id' => $model->getId())
                );
                $this->getResponse()->representJson($body);
            } else {
                if ($hasError) {
                    $this->_redirect('catalog/*/add');
                } else {
                    $this->_redirect('catalog/*/edit', array('id' => $model->getId()));
                }
            }
        } else {
            $response = array();
            if ($hasError) {
                $this->_view->getLayout()->initMessages();
                $response['error'] = 1;
                $response['message'] = $this->_view->getLayout()->getMessagesBlock()->getGroupedHtml();
            } else {
                $response['error'] = 0;
                $response['url'] = $this->getUrl('catalog/*/');
            }
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response)
            );
        }
    }
}
