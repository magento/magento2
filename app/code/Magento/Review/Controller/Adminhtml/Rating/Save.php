<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Rating;

class Save extends \Magento\Review\Controller\Adminhtml\Rating
{
    /**
     * Save rating
     *
     * @return void
     */
    public function execute()
    {
        $this->_initEnityId();

        if ($this->getRequest()->getPost()) {
            try {
                $ratingModel = $this->_objectManager->create('Magento\Review\Model\Rating');

                $stores = $this->getRequest()->getParam('stores');
                $position = (int)$this->getRequest()->getParam('position');
                $stores[] = 0;
                $isActive = (bool)$this->getRequest()->getParam('is_active');
                $ratingModel->setRatingCode(
                    $this->getRequest()->getParam('rating_code')
                )->setRatingCodes(
                    $this->getRequest()->getParam('rating_codes')
                )->setStores(
                    $stores
                )->setPosition(
                    $position
                )->setId(
                    $this->getRequest()->getParam('id')
                )->setIsActive(
                    $isActive
                )->setEntityId(
                    $this->_coreRegistry->registry('entityId')
                )->save();

                $options = $this->getRequest()->getParam('option_title');

                if (is_array($options)) {
                    $i = 1;
                    foreach ($options as $key => $optionCode) {
                        $optionModel = $this->_objectManager->create('Magento\Review\Model\Rating\Option');
                        if (!preg_match("/^add_([0-9]*?)$/", $key)) {
                            $optionModel->setId($key);
                        }

                        $optionModel->setCode(
                            $optionCode
                        )->setValue(
                            $i
                        )->setRatingId(
                            $ratingModel->getId()
                        )->setPosition(
                            $i
                        )->save();
                        $i++;
                    }
                }

                $this->messageManager->addSuccess(__('You saved the rating.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setRatingData(false);

                $this->_redirect('review/rating/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_objectManager->get(
                    'Magento\Backend\Model\Session'
                )->setRatingData(
                    $this->getRequest()->getPost()
                );
                $this->_redirect('review/rating/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->_redirect('review/rating/');
    }
}
