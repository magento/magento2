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
namespace Magento\Review\Controller\Adminhtml\Product;

class Post extends \Magento\Review\Controller\Adminhtml\Product
{
    /**
     * @return void
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id', false);

        if ($data = $this->getRequest()->getPost()) {
            /** @var \Magento\Framework\StoreManagerInterface $storeManagerInterface */
            $storeManager = $this->_objectManager->get('Magento\Framework\StoreManagerInterface');
            if ($storeManager->hasSingleStore()) {
                $data['stores'] = array(
                    $storeManager->getStore(true)->getId()
                );
            } elseif (isset($data['select_stores'])) {
                $data['stores'] = $data['select_stores'];
            }

            $review = $this->_reviewFactory->create()->setData($data);

            try {
                $review->setEntityId(1) // product
                    ->setEntityPkValue($productId)
                    ->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID)
                    ->setStatusId($data['status_id'])
                    ->setCustomerId(null)//null is for administrator only
                    ->save();

                $arrRatingId = $this->getRequest()->getParam('ratings', array());
                foreach ($arrRatingId as $ratingId => $optionId) {
                    $this->_ratingFactory->create(
                    )->setRatingId(
                        $ratingId
                    )->setReviewId(
                        $review->getId()
                    )->addOptionVote(
                        $optionId,
                        $productId
                    );
                }

                $review->aggregate();

                $this->messageManager->addSuccess(__('You saved the review.'));
                if ($this->getRequest()->getParam('ret') == 'pending') {
                    $this->getResponse()->setRedirect($this->getUrl('review/*/pending'));
                } else {
                    $this->getResponse()->setRedirect($this->getUrl('review/*/'));
                }

                return;
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('An error occurred while saving review.'));
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('review/*/'));
        return;
    }
}
