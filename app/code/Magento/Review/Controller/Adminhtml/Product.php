<?php
/**
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
 * @category    Magento
 * @package     Magento_Review
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Review\Controller\Adminhtml;

/**
 * Reviews admin controller
 */
class Product extends \Magento\Backend\App\Action
{
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = array('edit');

    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Registry $coreRegistry
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Registry $coreRegistry)
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Customer Reviews'));

        $this->_title->add(__('Reviews'));

        if ($this->getRequest()->getParam('ajax')) {
            return $this->_forward('reviewGrid');
        }

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Review::catalog_reviews_ratings_reviews_all');

        $this->_addContent($this->_view->getLayout()->createBlock('Magento\Review\Block\Adminhtml\Main'));

        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function pendingAction()
    {
        $this->_title->add(__('Customer Reviews'));

        $this->_title->add(__('Pending Reviews'));

        if ($this->getRequest()->getParam('ajax')) {
            $this->_coreRegistry->register('usePendingFilter', true);
            return $this->_forward('reviewGrid');
        }

        $this->_view->loadLayout();

        $this->_coreRegistry->register('usePendingFilter', true);
        $this->_addContent($this->_view->getLayout()->createBlock('Magento\Review\Block\Adminhtml\Main'));

        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function editAction()
    {
        $this->_title->add(__('Customer Reviews'));

        $this->_title->add(__('Edit Review'));

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Review::catalog_reviews_ratings_reviews_all');

        $this->_addContent($this->_view->getLayout()->createBlock('Magento\Review\Block\Adminhtml\Edit'));

        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function newAction()
    {
        $this->_title->add(__('Customer Reviews'));

        $this->_title->add(__('New Review'));

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Review::catalog_reviews_ratings_reviews_all');

        $this->_view->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->_addContent($this->_view->getLayout()->createBlock('Magento\Review\Block\Adminhtml\Add'));
        $this->_addContent($this->_view->getLayout()->createBlock('Magento\Review\Block\Adminhtml\Product\Grid'));

        $this->_view->renderLayout();
    }

    /**
     * @return mixed
     */
    public function saveAction()
    {
        if (($data = $this->getRequest()->getPost()) && ($reviewId = $this->getRequest()->getParam('id'))) {
            $review = $this->_objectManager->create('Magento\Review\Model\Review')->load($reviewId);
            if (!$review->getId()) {
                $this->messageManager->addError(__('The review was removed by another user or does not exist.'));
            } else {
                try {
                    $review->addData($data)->save();

                    $arrRatingId = $this->getRequest()->getParam('ratings', array());
                    $votes = $this->_objectManager->create(
                        'Magento\Rating\Model\Rating\Option\Vote'
                    )->getResourceCollection()->setReviewFilter(
                        $reviewId
                    )->addOptionInfo()->load()->addRatingOptions();
                    foreach ($arrRatingId as $ratingId => $optionId) {
                        if ($vote = $votes->getItemByColumnValue('rating_id', $ratingId)) {
                            $this->_objectManager->create(
                                'Magento\Rating\Model\Rating'
                            )->setVoteId(
                                $vote->getId()
                            )->setReviewId(
                                $review->getId()
                            )->updateOptionVote(
                                $optionId
                            );
                        } else {
                            $this->_objectManager->create(
                                'Magento\Rating\Model\Rating'
                            )->setRatingId(
                                $ratingId
                            )->setReviewId(
                                $review->getId()
                            )->addOptionVote(
                                $optionId,
                                $review->getEntityPkValue()
                            );
                        }
                    }

                    $review->aggregate();

                    $this->messageManager->addSuccess(__('You saved the review.'));
                } catch (\Magento\Model\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('Something went wrong while saving this review.'));
                }
            }

            $nextId = (int)$this->getRequest()->getParam('next_item');
            $url = $this->getUrl($this->getRequest()->getParam('ret') == 'pending' ? '*/*/pending' : '*/*/');
            if ($nextId) {
                $url = $this->getUrl('review/*/edit', array('id' => $nextId));
            }
            return $this->getResponse()->setRedirect($url);
        }
        $this->_redirect('review/*/');
    }

    /**
     * @return void
     */
    public function deleteAction()
    {
        $reviewId = $this->getRequest()->getParam('id', false);
        try {
            $this->_objectManager->create('Magento\Review\Model\Review')->setId($reviewId)->aggregate()->delete();

            $this->messageManager->addSuccess(__('The review has been deleted.'));
            if ($this->getRequest()->getParam('ret') == 'pending') {
                $this->getResponse()->setRedirect($this->getUrl('review/*/pending'));
            } else {
                $this->getResponse()->setRedirect($this->getUrl('review/*/'));
            }
            return;
        } catch (\Magento\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong  deleting this review.'));
        }

        $this->_redirect('review/*/edit/', array('id' => $reviewId));
    }

    /**
     * @return void
     */
    public function massDeleteAction()
    {
        $reviewsIds = $this->getRequest()->getParam('reviews');

        if (!is_array($reviewsIds)) {
            $this->messageManager->addError(__('Please select review(s).'));
        } else {
            try {
                foreach ($reviewsIds as $reviewId) {
                    $model = $this->_objectManager->create('Magento\Review\Model\Review')->load($reviewId);
                    $model->delete();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been deleted.', count($reviewsIds))
                );
            } catch (\Magento\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('An error occurred while deleting record(s).'));
            }
        }

        $this->_redirect('review/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    /**
     * @return void
     */
    public function massUpdateStatusAction()
    {
        $reviewsIds = $this->getRequest()->getParam('reviews');
        if (!is_array($reviewsIds)) {
            $this->messageManager->addError(__('Please select review(s).'));
        } else {
            try {
                $status = $this->getRequest()->getParam('status');
                foreach ($reviewsIds as $reviewId) {
                    $model = $this->_objectManager->create('Magento\Review\Model\Review')->load($reviewId);
                    $model->setStatusId($status)->save()->aggregate();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been updated.', count($reviewsIds))
                );
            } catch (\Magento\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('An error occurred while updating the selected review(s).')
                );
            }
        }

        $this->_redirect('review/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    /**
     * @return void
     */
    public function massVisibleInAction()
    {
        $reviewsIds = $this->getRequest()->getParam('reviews');

        if (!is_array($reviewsIds)) {
            $this->messageManager->addError(__('Please select review(s).'));
        } else {
            try {
                $stores = $this->getRequest()->getParam('stores');
                foreach ($reviewsIds as $reviewId) {
                    $model = $this->_objectManager->create('Magento\Review\Model\Review')->load($reviewId);
                    $model->setSelectStores($stores);
                    $model->save();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been updated.', count($reviewsIds))
                );
            } catch (\Magento\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('An error occurred while updating the selected review(s).')
                );
            }
        }

        $this->_redirect('review/*/pending');
    }

    /**
     * @return void
     */
    public function productGridAction()
    {
        $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock('Magento\Review\Block\Adminhtml\Product\Grid')->toHtml()
        );
    }

    /**
     * @return void
     */
    public function reviewGridAction()
    {
        $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock('Magento\Review\Block\Adminhtml\Grid')->toHtml()
        );
    }

    /**
     * @return void
     */
    public function jsonProductInfoAction()
    {
        $response = new \Magento\Object();
        $id = $this->getRequest()->getParam('id');
        if (intval($id) > 0) {
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($id);

            $response->setId($id);
            $response->addData($product->getData());
            $response->setError(0);
        } else {
            $response->setError(1);
            $response->setMessage(__('We can\'t get the product ID.'));
        }
        $this->getResponse()->setBody($response->toJSON());
    }

    /**
     * @return void
     */
    public function postAction()
    {
        $productId = $this->getRequest()->getParam('product_id', false);

        if ($data = $this->getRequest()->getPost()) {
            if ($this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->hasSingleStore()) {
                $data['stores'] = array(
                    $this->_objectManager->get('Magento\Core\Model\StoreManager')->getStore(true)->getId()
                );
            } elseif (isset($data['select_stores'])) {
                $data['stores'] = $data['select_stores'];
            }

            $review = $this->_objectManager->create('Magento\Review\Model\Review')->setData($data);

            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);

            try {
                $review->setEntityId(1) // product
                    ->setEntityPkValue($productId)
                    ->setStoreId($product->getStoreId())
                    ->setStatusId($data['status_id'])
                    ->setCustomerId(null)//null is for administrator only
                    ->save();

                $arrRatingId = $this->getRequest()->getParam('ratings', array());
                foreach ($arrRatingId as $ratingId => $optionId) {
                    $this->_objectManager->create(
                        'Magento\Rating\Model\Rating'
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
            } catch (\Magento\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('An error occurred while saving review.'));
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('review/*/'));
        return;
    }

    /**
     * @return void
     */
    public function ratingItemsAction()
    {
        $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(
                'Magento\Review\Block\Adminhtml\Rating\Detailed'
            )->setIndependentMode()->toHtml()
        );
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'pending':
                return $this->_authorization->isAllowed('Magento_Review::pending');
                break;
            default:
                return $this->_authorization->isAllowed('Magento_Review::reviews_all');
                break;
        }
    }
}
