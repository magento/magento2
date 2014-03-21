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
 * @package     Magento_Rating
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Rating\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Admin ratings controller
 */
class Index extends \Magento\Backend\App\Action
{
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
        $this->_initEnityId();
        $this->_view->loadLayout();

        $this->_setActiveMenu('Magento_Review::catalog_reviews_ratings_ratings');
        $this->_addBreadcrumb(__('Manage Ratings'), __('Manage Ratings'));

        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function editAction()
    {
        $this->_initEnityId();
        $this->_view->loadLayout();

        $ratingModel = $this->_objectManager->create('Magento\Rating\Model\Rating');
        if ($this->getRequest()->getParam('id')) {
            $ratingModel->load($this->getRequest()->getParam('id'));
        }

        $this->_title->add($ratingModel->getId() ? $ratingModel->getRatingCode() : __('New Rating'));

        $this->_setActiveMenu('Magento_Review::catalog_reviews_ratings_ratings');
        $this->_addBreadcrumb(__('Manage Ratings'), __('Manage Ratings'));

        $this->_addContent(
            $this->_view->getLayout()->createBlock('Magento\Rating\Block\Adminhtml\Edit')
        )->_addLeft(
            $this->_view->getLayout()->createBlock('Magento\Rating\Block\Adminhtml\Edit\Tabs')
        );
        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Save rating
     *
     * @return void
     */
    public function saveAction()
    {
        $this->_initEnityId();

        if ($this->getRequest()->getPost()) {
            try {
                $ratingModel = $this->_objectManager->create('Magento\Rating\Model\Rating');

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
                        $optionModel = $this->_objectManager->create('Magento\Rating\Model\Rating\Option');
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

                $this->_redirect('rating/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_objectManager->get(
                    'Magento\Backend\Model\Session'
                )->setRatingData(
                    $this->getRequest()->getPost()
                );
                $this->_redirect('rating/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('rating/*/');
    }

    /**
     * @return void
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = $this->_objectManager->create('Magento\Rating\Model\Rating');
                /* @var $model \Magento\Rating\Model\Rating */
                $model->load($this->getRequest()->getParam('id'))->delete();
                $this->messageManager->addSuccess(__('You deleted the rating.'));
                $this->_redirect('rating/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('rating/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('rating/*/');
    }

    /**
     * @return void
     */
    protected function _initEnityId()
    {
        $this->_title->add(__('Ratings'));

        $this->_coreRegistry->register(
            'entityId',
            $this->_objectManager->create('Magento\Rating\Model\Rating\Entity')->getIdByCode('product')
        );
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Rating::ratings');
    }
}
