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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping;

use Magento\App\RequestInterface;

/**
 * GoogleShopping Admin Item Types Controller
 */
class Types extends \Magento\Backend\App\Action
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
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Dispatches controller_action_postdispatch_adminhtml Event
     *
     * @param RequestInterface $request
     * @return \Magento\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $response = parent::dispatch($request);
        if (!$this->_actionFlag->get('', self::FLAG_NO_POST_DISPATCH)) {
            $this->_eventManager->dispatch(
                'controller_action_postdispatch_adminhtml',
                array('controller_action' => $this)
            );
        }
        return $response;

    }

    /**
     * Initialize attribute set mapping object
     *
     * @return $this
     */
    protected function _initItemType()
    {
        $this->_title->add(__('Google Content Attributes'));

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
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_GoogleShopping::catalog_googleshopping_types')
            ->_addBreadcrumb(__('Catalog'), __('Catalog'))
            ->_addBreadcrumb(__('Google Content'), __('Google Content'));
        return $this;
    }

    /**
     * List of all maps (items)
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Google Content Attributes'));

        $this->_initAction()
            ->_addBreadcrumb(__('Attribute Maps'), __('Attribute Maps'));
        $this->_view->renderLayout();
    }

    /**
     * Grid for AJAX request
     *
     * @return void
     */
    public function gridAction()
    {
        $this->_view->loadLayout('false');
        $this->_view->renderLayout();
    }

    /**
     * Create new attribute set mapping
     *
     * @return void
     */
    public function newAction()
    {
        try {
            $this->_initItemType();

            $this->_title->add(__('New Google Content Attribute Mapping'));

            $this->_initAction()
                ->_addBreadcrumb(__('New attribute set mapping'), __('New attribute set mapping'))
                ->_addContent($this->_view->getLayout()
                    ->createBlock('Magento\GoogleShopping\Block\Adminhtml\Types\Edit')
                );
            $this->_view->renderLayout();
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Logger')->logException($e);
            $this->messageManager->addError(__("We can't create Attribute Set Mapping."));
            $this->_redirect('adminhtml/*/index', array('store' => $this->_getStore()->getId()));
        }
    }

    /**
     * Edit attribute set mapping
     *
     * @return void
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

            $this->_title->add(__('Google Content Attribute Mapping'));
            $this->_coreRegistry->register('attributes', $result);

            $breadcrumbLabel = $typeId ? __('Edit attribute set mapping') : __('New attribute set mapping');
            $this->_initAction()
                ->_addBreadcrumb($breadcrumbLabel, $breadcrumbLabel)
                ->_addContent($this->_view->getLayout()->createBlock(
                    'Magento\GoogleShopping\Block\Adminhtml\Types\Edit'
                    )
                );
            $this->_view->renderLayout();
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Logger')->logException($e);
            $this->messageManager->addError(__("We can't edit Attribute Set Mapping."));
            $this->_redirect('adminhtml/*/index');
        }
    }

    /**
     * Save attribute set mapping
     *
     * @return void
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

            $this->messageManager->addSuccess(__('The attribute mapping has been saved.'));
            if (!empty($requiredAttributes)) {
                $this->messageManager
                    ->addSuccess($this->_objectManager->get('Magento\GoogleShopping\Helper\Category')->getMessage());
            }
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Logger')->logException($e);
            $this->messageManager->addError(__("We can't save Attribute Set Mapping."));
        }
        $this->_redirect('adminhtml/*/index', array('store' => $this->_getStore()->getId()));
    }

    /**
     * Delete attribute set mapping
     *
     * @return void
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
            $this->messageManager->addSuccess(__('Attribute set mapping was deleted'));
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Logger')->logException($e);
            $this->messageManager->addError(__("We can't delete Attribute Set Mapping."));
        }
        $this->_redirect('adminhtml/*/index', array('store' => $this->_getStore()->getId()));
    }

    /**
     * Get Google Content attributes list
     *
     * @return void
     */
    public function loadAttributesAction()
    {
        try {
            $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock('Magento\GoogleShopping\Block\Adminhtml\Types\Edit\Attributes')
                ->setAttributeSetId($this->getRequest()->getParam('attribute_set_id'))
                ->setTargetCountry($this->getRequest()->getParam('target_country'))
                ->setAttributeSetSelected(true)
                ->toHtml()
            );
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Logger')->logException($e);
            // just need to output text with error
            $this->messageManager->addError(__("We can't load attributes."));
        }
    }

    /**
     * Get available attribute sets
     *
     * @return void
     */
    protected function loadAttributeSetsAction()
    {
        try {
            $this->getResponse()->setBody(
                $this->_view->getLayout()->getBlockSingleton('Magento\GoogleShopping\Block\Adminhtml\Types\Edit\Form')
                    ->getAttributeSetsSelectElement($this->getRequest()->getParam('target_country'))
                    ->toHtml()
            );
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Logger')->logException($e);
            // just need to output text with error
            $this->messageManager->addError(__("We can't load attribute sets."));
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
