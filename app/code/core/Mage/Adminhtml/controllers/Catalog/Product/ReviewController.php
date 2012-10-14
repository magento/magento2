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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Reviews admin controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Catalog_Product_ReviewController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = array('edit');

    public function indexAction()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Reviews and Ratings'))
             ->_title($this->__('Customer Reviews'));

        $this->_title($this->__('All Reviews'));

        if ($this->getRequest()->getParam('ajax')) {
            return $this->_forward('reviewGrid');
        }

        $this->loadLayout();
        $this->_setActiveMenu('Mage_Review::catalog_reviews_ratings_reviews_all');

        $this->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Review_Main'));

        $this->renderLayout();
    }

    public function pendingAction()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Reviews and Ratings'))
             ->_title($this->__('Customer Reviews'));

        $this->_title($this->__('Pending Reviews'));

        if ($this->getRequest()->getParam('ajax')) {
            Mage::register('usePendingFilter', true);
            return $this->_forward('reviewGrid');
        }

        $this->loadLayout();
        $this->_setActiveMenu('Mage_Review::catalog_reviews_ratings_reviews_pending');

        Mage::register('usePendingFilter', true);
        $this->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Review_Main'));

        $this->renderLayout();
    }

    public function editAction()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Reviews and Ratings'))
             ->_title($this->__('Customer Reviews'));

        $this->_title($this->__('Edit Review'));

        $this->loadLayout();
        $this->_setActiveMenu('Mage_Review::catalog_reviews_ratings_reviews_all');

        $this->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Review_Edit'));

        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Reviews and Ratings'))
             ->_title($this->__('Customer Reviews'));

        $this->_title($this->__('New Review'));

        $this->loadLayout();
        $this->_setActiveMenu('Mage_Review::catalog_reviews_ratings_reviews_all');

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Review_Add'));
        $this->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Review_Product_Grid'));

        $this->renderLayout();
    }

    public function saveAction()
    {
        if (($data = $this->getRequest()->getPost()) && ($reviewId = $this->getRequest()->getParam('id'))) {
            $review = Mage::getModel('Mage_Review_Model_Review')->load($reviewId);
            $session = Mage::getSingleton('Mage_Adminhtml_Model_Session');
            if (! $review->getId()) {
                $session->addError(Mage::helper('Mage_Catalog_Helper_Data')->__('The review was removed by another user or does not exist.'));
            } else {
                try {
                    $review->addData($data)->save();

                    $arrRatingId = $this->getRequest()->getParam('ratings', array());
                    $votes = Mage::getModel('Mage_Rating_Model_Rating_Option_Vote')
                        ->getResourceCollection()
                        ->setReviewFilter($reviewId)
                        ->addOptionInfo()
                        ->load()
                        ->addRatingOptions();
                    foreach ($arrRatingId as $ratingId=>$optionId) {
                        if($vote = $votes->getItemByColumnValue('rating_id', $ratingId)) {
                            Mage::getModel('Mage_Rating_Model_Rating')
                                ->setVoteId($vote->getId())
                                ->setReviewId($review->getId())
                                ->updateOptionVote($optionId);
                        } else {
                            Mage::getModel('Mage_Rating_Model_Rating')
                                ->setRatingId($ratingId)
                                ->setReviewId($review->getId())
                                ->addOptionVote($optionId, $review->getEntityPkValue());
                        }
                    }

                    $review->aggregate();

                    $session->addSuccess(Mage::helper('Mage_Catalog_Helper_Data')->__('The review has been saved.'));
                } catch (Mage_Core_Exception $e) {
                    $session->addError($e->getMessage());
                } catch (Exception $e){
                    $session->addException($e, Mage::helper('Mage_Catalog_Helper_Data')->__('An error occurred while saving this review.'));
                }
            }

            $nextId = (int) $this->getRequest()->getParam('next_item');
            $url = $this->getUrl($this->getRequest()->getParam('ret') == 'pending' ? '*/*/pending' : '*/*/');
            if ($nextId) {
                $url = $this->getUrl('*/*/edit', array('id' => $nextId));
            }
            return $this->getResponse()->setRedirect($url);
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $reviewId   = $this->getRequest()->getParam('id', false);
        $session    = Mage::getSingleton('Mage_Adminhtml_Model_Session');

        try {
            Mage::getModel('Mage_Review_Model_Review')->setId($reviewId)
                ->aggregate()
                ->delete();

            $session->addSuccess(Mage::helper('Mage_Catalog_Helper_Data')->__('The review has been deleted'));
            if( $this->getRequest()->getParam('ret') == 'pending' ) {
                $this->getResponse()->setRedirect($this->getUrl('*/*/pending'));
            } else {
                $this->getResponse()->setRedirect($this->getUrl('*/*/'));
            }
            return;
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        } catch (Exception $e){
            $session->addException($e, Mage::helper('Mage_Catalog_Helper_Data')->__('An error occurred while deleting this review.'));
        }

        $this->_redirect('*/*/edit/',array('id'=>$reviewId));
    }

    public function massDeleteAction()
    {
        $reviewsIds = $this->getRequest()->getParam('reviews');
        $session    = Mage::getSingleton('Mage_Adminhtml_Model_Session');

        if(!is_array($reviewsIds)) {
             $session->addError(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Please select review(s).'));
        } else {
            try {
                foreach ($reviewsIds as $reviewId) {
                    $model = Mage::getModel('Mage_Review_Model_Review')->load($reviewId);
                    $model->delete();
                }
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(
                    Mage::helper('Mage_Adminhtml_Helper_Data')->__('Total of %d record(s) have been deleted.', count($reviewsIds))
                );
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e){
                $session->addException($e, Mage::helper('Mage_Adminhtml_Helper_Data')->__('An error occurred while deleting record(s).'));
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    public function massUpdateStatusAction()
    {
        $reviewsIds = $this->getRequest()->getParam('reviews');
        $session    = Mage::getSingleton('Mage_Adminhtml_Model_Session');

        if(!is_array($reviewsIds)) {
             $session->addError(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Please select review(s).'));
        } else {
            /* @var $session Mage_Adminhtml_Model_Session */
            try {
                $status = $this->getRequest()->getParam('status');
                foreach ($reviewsIds as $reviewId) {
                    $model = Mage::getModel('Mage_Review_Model_Review')->load($reviewId);
                    $model->setStatusId($status)
                        ->save()
                        ->aggregate();
                }
                $session->addSuccess(
                    Mage::helper('Mage_Adminhtml_Helper_Data')->__('Total of %d record(s) have been updated.', count($reviewsIds))
                );
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                $session->addException($e, Mage::helper('Mage_Adminhtml_Helper_Data')->__('An error occurred while updating the selected review(s).'));
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    public function massVisibleInAction()
    {
        $reviewsIds = $this->getRequest()->getParam('reviews');
        $session    = Mage::getSingleton('Mage_Adminhtml_Model_Session');

        if(!is_array($reviewsIds)) {
             $session->addError(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Please select review(s).'));
        } else {
            $session = Mage::getSingleton('Mage_Adminhtml_Model_Session');
            /* @var $session Mage_Adminhtml_Model_Session */
            try {
                $stores = $this->getRequest()->getParam('stores');
                foreach ($reviewsIds as $reviewId) {
                    $model = Mage::getModel('Mage_Review_Model_Review')->load($reviewId);
                    $model->setSelectStores($stores);
                    $model->save();
                }
                $session->addSuccess(
                    Mage::helper('Mage_Adminhtml_Helper_Data')->__('Total of %d record(s) have been updated.', count($reviewsIds))
                );
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                $session->addException($e, Mage::helper('Mage_Adminhtml_Helper_Data')->__('An error occurred while updating the selected review(s).'));
            }
        }

        $this->_redirect('*/*/pending');
    }

    public function productGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Review_Product_Grid')->toHtml()
        );
    }

    public function reviewGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Review_Grid')->toHtml()
        );
    }

    public function jsonProductInfoAction()
    {
        $response = new Varien_Object();
        $id = $this->getRequest()->getParam('id');
        if( intval($id) > 0 ) {
            $product = Mage::getModel('Mage_Catalog_Model_Product')
                ->load($id);

            $response->setId($id);
            $response->addData($product->getData());
            $response->setError(0);
        } else {
            $response->setError(1);
            $response->setMessage(Mage::helper('Mage_Catalog_Helper_Data')->__('Unable to get the product ID.'));
        }
        $this->getResponse()->setBody($response->toJSON());
    }

    public function postAction()
    {
        $productId  = $this->getRequest()->getParam('product_id', false);
        $session    = Mage::getSingleton('Mage_Adminhtml_Model_Session');

        if ($data = $this->getRequest()->getPost()) {
            if (Mage::app()->hasSingleStore()) {
                $data['stores'] = array(Mage::app()->getStore(true)->getId());
            } else  if (isset($data['select_stores'])) {
                $data['stores'] = $data['select_stores'];
            }

            $review = Mage::getModel('Mage_Review_Model_Review')->setData($data);

            $product = Mage::getModel('Mage_Catalog_Model_Product')
                ->load($productId);

            try {
                $review->setEntityId(1) // product
                    ->setEntityPkValue($productId)
                    ->setStoreId($product->getStoreId())
                    ->setStatusId($data['status_id'])
                    ->setCustomerId(null)//null is for administrator only
                    ->save();

                $arrRatingId = $this->getRequest()->getParam('ratings', array());
                foreach ($arrRatingId as $ratingId=>$optionId) {
                    Mage::getModel('Mage_Rating_Model_Rating')
                       ->setRatingId($ratingId)
                       ->setReviewId($review->getId())
                       ->addOptionVote($optionId, $productId);
                }

                $review->aggregate();

                $session->addSuccess(Mage::helper('Mage_Catalog_Helper_Data')->__('The review has been saved.'));
                if( $this->getRequest()->getParam('ret') == 'pending' ) {
                    $this->getResponse()->setRedirect($this->getUrl('*/*/pending'));
                } else {
                    $this->getResponse()->setRedirect($this->getUrl('*/*/'));
                }

                return;
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                $session->addException($e, Mage::helper('Mage_Adminhtml_Helper_Data')->__('An error occurred while saving review.'));
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/'));
        return;
    }

    public function ratingItemsAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('Mage_Adminhtml_Block_Review_Rating_Detailed')
                ->setIndependentMode()
                ->toHtml()
        );
    }

    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'pending':
                return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Review::pending');
                break;
            default:
                return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Review::reviews_all');
                break;
        }
    }
}
