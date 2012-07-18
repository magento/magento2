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
 * Admin ratings controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_RatingController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_initEnityId();
        $this->loadLayout();

        $this->_setActiveMenu('Mage_Review::catalog_reviews_ratings_ratings');
        $this->_addBreadcrumb(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Manage Ratings'), Mage::helper('Mage_Adminhtml_Helper_Data')->__('Manage Ratings'));
        $this->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Rating_Rating'));

        $this->renderLayout();
    }

    public function editAction()
    {
        $this->_initEnityId();
        $this->loadLayout();

        $ratingModel = Mage::getModel('Mage_Rating_Model_Rating');
        if ($this->getRequest()->getParam('id')) {
            $ratingModel->load($this->getRequest()->getParam('id'));
        }

        $this->_title($ratingModel->getId() ? $ratingModel->getRatingCode() : $this->__('New Rating'));

        $this->_setActiveMenu('Mage_Review::catalog_reviews_ratings_ratings');
        $this->_addBreadcrumb(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Manage Ratings'), Mage::helper('Mage_Adminhtml_Helper_Data')->__('Manage Ratings'));

        $this->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Rating_Edit'))
            ->_addLeft($this->getLayout()->createBlock('Mage_Adminhtml_Block_Rating_Edit_Tabs'));
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Save rating
     */
    public function saveAction()
    {
        $this->_initEnityId();

        if ($this->getRequest()->getPost()) {
            try {
                $ratingModel = Mage::getModel('Mage_Rating_Model_Rating');

                $stores = $this->getRequest()->getParam('stores');
                $position = (int)$this->getRequest()->getParam('position');
                $stores[] = 0;
                $ratingModel->setRatingCode($this->getRequest()->getParam('rating_code'))
                    ->setRatingCodes($this->getRequest()->getParam('rating_codes'))
                    ->setStores($stores)
                    ->setPosition($position)
                    ->setId($this->getRequest()->getParam('id'))
                    ->setEntityId(Mage::registry('entityId'))
                    ->save();

                $options = $this->getRequest()->getParam('option_title');

                if (is_array($options)) {
                    $i = 1;
                    foreach ($options as $key => $optionCode) {
                        $optionModel = Mage::getModel('Mage_Rating_Model_Rating_Option');
                        if (!preg_match("/^add_([0-9]*?)$/", $key)) {
                            $optionModel->setId($key);
                        }

                        $optionModel->setCode($optionCode)
                            ->setValue($i)
                            ->setRatingId($ratingModel->getId())
                            ->setPosition($i)
                            ->save();
                        $i++;
                    }
                }

                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(Mage::helper('Mage_Adminhtml_Helper_Data')->__('The rating has been saved.'));
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->setRatingData(false);

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->setRatingData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $model = Mage::getModel('Mage_Rating_Model_Rating');
                /* @var $model Mage_Rating_Model_Rating */
                $model->load($this->getRequest()->getParam('id'))
                    ->delete();
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(Mage::helper('Mage_Adminhtml_Helper_Data')->__('The rating has been deleted.'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    protected function _initEnityId()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Reviews and Ratings'))
             ->_title($this->__('Manage Ratings'));

        Mage::register('entityId', Mage::getModel('Mage_Rating_Model_Rating_Entity')->getIdByCode('product'));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Backend_Model_Auth_Session')->isAllowed('catalog/reviews_ratings/ratings');
    }

}
