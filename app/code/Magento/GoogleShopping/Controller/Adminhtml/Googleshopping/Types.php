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
 * @package     Magento_GoogleShopping
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * GoogleShopping Admin Item Types Controller
*/
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping;

class Types extends \Magento\Adminhtml\Controller\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Controller\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\Controller\Context $context,
        \Magento\Core\Model\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Dispatches controller_action_postdispatch_adminhtml Event (as not Adminhtml router)
     */
    public function postDispatch()
    {
        parent::postDispatch();
        if ($this->getFlag('', self::FLAG_NO_POST_DISPATCH)) {
            return;
        }
        $this->_eventManager->dispatch('controller_action_postdispatch_adminhtml', array('controller_action' => $this));
    }

    /**
     * Initialize attribute set mapping object
     *
     * @return \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types
     */
    protected function _initItemType()
    {
        $this->_title(__('Google Content Attributes'));

        $this->_coreRegistry
            ->register('current_item_type', $this->_objectManager->create('Magento\GoogleShopping\Model\Type'));
        $typeId = $this->getRequest()->getParam('id');
        if (!is_null($typeId)) {
            $this->_coreRegistry->registry('current_item_type')->load($typeId);
        }
        return $this;
    }

    /**
     * Initialize general settings for action
     *
     * @return  \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('Magento_GoogleShopping::catalog_googleshopping_types')
            ->_addBreadcrumb(__('Catalog'), __('Catalog'))
            ->_addBreadcrumb(__('Google Content'), __('Google Content'));
        return $this;
    }

    /**
     * List of all maps (items)
     */
    public function indexAction()
    {
        $this->_title(__('Google Content Attributes'));

        $this->_initAction()
            ->_addBreadcrumb(__('Attribute Maps'), __('Attribute Maps'))
            ->renderLayout();
    }

    /**
     * Grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout('false');
        $this->renderLayout();
    }

    /**
     * Create new attribute set mapping
     */
    public function newAction()
    {
        try {
            $this->_initItemType();

            $this->_title(__('New Google Content Attribute Mapping'));

            $this->_initAction()
                ->_addBreadcrumb(__('New attribute set mapping'), __('New attribute set mapping'))
                ->_addContent($this->getLayout()->createBlock('Magento\GoogleShopping\Block\Adminhtml\Types\Edit'))
                ->renderLayout();
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            $this->_getSession()->addError(__("We can't create Attribute Set Mapping."));
            $this->_redirect('*/*/index', array('store' => $this->_getStore()->getId()));
        }
    }

    /**
     * Edit attribute set mapping
     */
    public function editAction()
    {
        $this->_initItemType();
        $typeId = $this->_coreRegistry->registry('current_item_type')->getTypeId();

        try {
            $result = array();
            if ($typeId) {
                $collection = $this->_objectManager
                    ->create('Magento\GoogleShopping\Model\Resource\Attribute\Collection')
                    ->addTypeFilter($typeId)
                    ->load();
                foreach ($collection as $attribute) {
                    $result[] = $attribute->getData();
                }
            }

            $this->_title(__('Google Content Attribute Mapping'));
            $this->_coreRegistry->register('attributes', $result);

            $breadcrumbLabel = $typeId ? __('Edit attribute set mapping') : __('New attribute set mapping');
            $this->_initAction()
                ->_addBreadcrumb($breadcrumbLabel, $breadcrumbLabel)
                ->_addContent($this->getLayout()->createBlock('Magento\GoogleShopping\Block\Adminhtml\Types\Edit'))
                ->renderLayout();
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            $this->_getSession()->addError(__("We can't edit Attribute Set Mapping."));
            $this->_redirect('*/*/index');
        }
    }

    /**
     * Save attribute set mapping
     */
    public function saveAction()
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
                $collection = $this->_objectManager
                    ->create('Magento\GoogleShopping\Model\Resource\Attribute\Collection')
                    ->addTypeFilter($typeModel->getId())
                    ->load();
                foreach ($collection as $attribute) {
                    $attribute->delete();
                }
            } else {
                $typeModel->setAttributeSetId($this->getRequest()->getParam('attribute_set_id'))
                    ->setTargetCountry($this->getRequest()->getParam('target_country'));
            }
            $typeModel->save();

            $attributes = $this->getRequest()->getParam('attributes');
            $requiredAttributes = $this->_objectManager->get('Magento\GoogleShopping\Model\Config')
                ->getRequiredAttributes();
            if (is_array($attributes)) {
                $typeId = $typeModel->getId();
                foreach ($attributes as $attrInfo) {
                    if (isset($attrInfo['delete']) && $attrInfo['delete'] == 1) {
                        continue;
                    }
                    $this->_objectManager->create('Magento\GoogleShopping\Model\Attribute')
                        ->setAttributeId($attrInfo['attribute_id'])
                        ->setGcontentAttribute($attrInfo['gcontent_attribute'])
                        ->setTypeId($typeId)
                        ->save();
                    unset($requiredAttributes[$attrInfo['gcontent_attribute']]);
                }
            }

            $this->_objectManager->get('Magento\Adminhtml\Model\Session')
                ->addSuccess(__('The attribute mapping has been saved.'));
            if (!empty($requiredAttributes)) {
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')
                    ->addSuccess($this->_objectManager->get('Magento\GoogleShopping\Helper\Category')->getMessage());
            }
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            $this->_objectManager->get('Magento\Adminhtml\Model\Session')
                ->addError(__("We can't save Attribute Set Mapping."));
        }
        $this->_redirect('*/*/index', array('store' => $this->_getStore()->getId()));
    }

    /**
     * Delete attribute set mapping
     */
    public function deleteAction()
    {
        try {
            $id = $this->getRequest()->getParam('id');
            $model = $this->_objectManager->create('Magento\GoogleShopping\Model\Type');
            $model->load($id);
            if ($model->getTypeId()) {
                $model->delete();
            }
            $this->_getSession()->addSuccess(__('Attribute set mapping was deleted'));
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            $this->_getSession()->addError(__("We can't delete Attribute Set Mapping."));
        }
        $this->_redirect('*/*/index', array('store' => $this->_getStore()->getId()));
    }

    /**
     * Get Google Content attributes list
     */
    public function loadAttributesAction()
    {
        try {
            $this->getResponse()->setBody(
            $this->getLayout()->createBlock('Magento\GoogleShopping\Block\Adminhtml\Types\Edit\Attributes')
                ->setAttributeSetId($this->getRequest()->getParam('attribute_set_id'))
                ->setTargetCountry($this->getRequest()->getParam('target_country'))
                ->setAttributeSetSelected(true)
                ->toHtml()
            );
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            // just need to output text with error
            $this->_getSession()->addError(__("We can't load attributes."));
        }
    }

    /**
     * Get available attribute sets
     */
    protected function loadAttributeSetsAction()
    {
        try {
            $this->getResponse()->setBody(
                $this->getLayout()->getBlockSingleton('Magento\GoogleShopping\Block\Adminhtml\Types\Edit\Form')
                    ->getAttributeSetsSelectElement($this->getRequest()->getParam('target_country'))
                    ->toHtml()
            );
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            // just need to output text with error
            $this->_getSession()->addError(__("We can't load attribute sets."));
        }
    }

    /**
     * Get store object, basing on request
     *
     * @return \Magento\Core\Model\Store
     */
    public function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        if ($storeId == 0) {
            return $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->getDefaultStoreView();
        }
        return $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->getStore($storeId);
    }

    /**
     * Check access to this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_GoogleShopping::types');
    }
}
