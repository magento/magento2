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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product attribute controller
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

class Attribute extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $_attributeLabelCache;

    /**
     * @var string
     */
    protected $_entityTypeId;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Cache\FrontendInterface $attributeLabelCache
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Cache\FrontendInterface $attributeLabelCache,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_attributeLabelCache = $attributeLabelCache;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $this->_entityTypeId = $this->_objectManager->create(
            'Magento\Eav\Model\Entity'
        )->setType(
            \Magento\Catalog\Model\Product::ENTITY
        )->getTypeId();
        return parent::dispatch($request);
    }

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->_title->add(__('Product Attributes'));

        if ($this->getRequest()->getParam('popup')) {
            if ($this->getRequest()->getParam('product_tab') == 'variations') {
                $this->_view->loadLayout(
                    array('popup', 'catalog_product_attribute_edit_product_tab_variations_popup')
                );
            } else {
                $this->_view->loadLayout(array('popup', 'catalog_product_attribute_edit_popup'));
            }

            $this->_view->getLayout()->getBlock('root')->addBodyClass('attribute-popup');
        } else {
            $this->_view->loadLayout();
            $this->_addBreadcrumb(
                __('Catalog'),
                __('Catalog')
            )->_addBreadcrumb(
                __('Manage Product Attributes'),
                __('Manage Product Attributes')
            );
            $this->_setActiveMenu('Magento_Catalog::catalog_attributes_attributes');
        }

        return $this;
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->_initAction()->_addContent(
            $this->_view->getLayout()->createBlock('Magento\Catalog\Block\Adminhtml\Product\Attribute')
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
     * @return void
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        /** @var $model \Magento\Catalog\Model\Resource\Eav\Attribute */
        $model = $this->_objectManager->create(
            'Magento\Catalog\Model\Resource\Eav\Attribute'
        )->setEntityTypeId(
            $this->_entityTypeId
        );
        if ($id) {
            $model->load($id);

            if (!$model->getId()) {
                $this->messageManager->addError(__('This attribute no longer exists.'));
                $this->_redirect('catalog/*/');
                return;
            }

            // entity type check
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                $this->messageManager->addError(__('This attribute cannot be edited.'));
                $this->_redirect('catalog/*/');
                return;
            }
        }

        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getAttributeData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $attributeData = $this->getRequest()->getParam('attribute');
        if (!empty($attributeData) && $id === null) {
            $model->addData($attributeData);
        }

        $this->_coreRegistry->register('entity_attribute', $model);

        $this->_initAction();

        $this->_title->add($id ? $model->getName() : __('New Product Attribute'));

        $item = $id ? __('Edit Product Attribute') : __('New Product Attribute');

        $this->_addBreadcrumb($item, $item);

        $this->_view->getLayout()->getBlock(
            'attribute_edit_js'
        )->setIsPopup(
            (bool)$this->getRequest()->getParam('popup')
        );

        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function validateAction()
    {
        $response = new \Magento\Framework\Object();
        $response->setError(false);

        $attributeCode = $this->getRequest()->getParam('attribute_code');
        $frontendLabel = $this->getRequest()->getParam('frontend_label');
        $attributeCode = $attributeCode ?: $this->generateCode($frontendLabel[0]);
        $attributeId = $this->getRequest()->getParam('attribute_id');
        $attribute = $this->_objectManager->create(
            'Magento\Catalog\Model\Resource\Eav\Attribute'
        )->loadByCode(
            $this->_entityTypeId,
            $attributeCode
        );

        if ($attribute->getId() && !$attributeId) {
            if (strlen($this->getRequest()->getParam('attribute_code'))) {
                $response->setAttributes(array('attribute_code' => __('An attribute with this code already exists.')));
            } else {
                $response->setAttributes(
                    array('attribute_label' => __('Attribute with the same code (%1) already exists.', $attributeCode))
                );
            }
            $response->setError(true);
        }
        if ($this->getRequest()->has('new_attribute_set_name')) {
            $setName = $this->getRequest()->getParam('new_attribute_set_name');
            /** @var $attributeSet \Magento\Eav\Model\Entity\Attribute\Set */
            $attributeSet = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute\Set');
            $attributeSet->setEntityTypeId($this->_entityTypeId)->load($setName, 'attribute_set_name');
            if ($attributeSet->getId()) {
                $setName = $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($setName);
                $this->messageManager->addError(__('Attribute Set with name \'%1\' already exists.', $setName));

                $this->_view->getLayout()->initMessages();
                $response->setError(true);
                $response->setHtmlMessage($this->_view->getLayout()->getMessagesBlock()->getGroupedHtml());
            }
        }
        $this->getResponse()->representJson($response->toJson());
    }

    /**
     * Generate code from label
     *
     * @param string $label
     * @return string
     */
    private function generateCode($label)
    {
        $code = substr(
            preg_replace(
                '/[^a-z_0-9]/',
                '_',
                $this->_objectManager->create('Magento\Catalog\Model\Product\Url')->formatUrlKey($label)
            ),
            0,
            30
        );
        $validatorAttrCode = new \Zend_Validate_Regex(array('pattern' => '/^[a-z][a-z_0-9]{0,29}[a-z0-9]$/'));
        if (!$validatorAttrCode->isValid($code)) {
            $code = 'attr_' . ($code ?: substr(md5(time()), 0, 8));
        }
        return $code;
    }

    /**
     * @return void
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            /** @var $session \Magento\Backend\Model\Auth\Session */
            $session = $this->_objectManager->get('Magento\Backend\Model\Session');

            $isNewAttributeSet = false;
            if (!empty($data['new_attribute_set_name'])) {
                /** @var $attributeSet \Magento\Eav\Model\Entity\Attribute\Set */
                $attributeSet = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute\Set');
                $name = $this->_objectManager->get(
                    'Magento\Framework\Filter\FilterManager'
                )->stripTags(
                    $data['new_attribute_set_name']
                );
                $name = trim($name);
                $attributeSet->setEntityTypeId($this->_entityTypeId)->load($name, 'attribute_set_name');

                if ($attributeSet->getId()) {
                    $this->messageManager->addError(__('Attribute Set with name \'%1\' already exists.', $name));
                    $this->messageManager->setAttributeData($data);
                    $this->_redirect('catalog/*/edit', array('_current' => true));
                    return;
                }

                try {
                    $attributeSet->setAttributeSetName($name)->validate();
                    $attributeSet->save();
                    $attributeSet->initFromSkeleton($this->getRequest()->getParam('set'))->save();
                    $isNewAttributeSet = true;
                } catch (\Magento\Framework\Model\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('Something went wrong saving the attribute.'));
                }
            }

            $redirectBack = $this->getRequest()->getParam('back', false);
            /* @var $model \Magento\Catalog\Model\Resource\Eav\Attribute */
            $model = $this->_objectManager->create('Magento\Catalog\Model\Resource\Eav\Attribute');
            /* @var $helper \Magento\Catalog\Helper\Product */
            $helper = $this->_objectManager->get('Magento\Catalog\Helper\Product');

            $id = $this->getRequest()->getParam('attribute_id');

            $attributeCode = $this->getRequest()->getParam('attribute_code');
            $frontendLabel = $this->getRequest()->getParam('frontend_label');
            $attributeCode = $attributeCode ?: $this->generateCode($frontendLabel[0]);
            if (strlen($this->getRequest()->getParam('attribute_code')) > 0) {
                $validatorAttrCode = new \Zend_Validate_Regex(array('pattern' => '/^[a-z][a-z_0-9]{0,30}$/'));
                if (!$validatorAttrCode->isValid($attributeCode)) {
                    $this->messageManager->addError(
                        __(
                            'Attribute code "%1" is invalid. Please use only letters (a-z), ' .
                            'numbers (0-9) or underscore(_) in this field, first character should be a letter.',
                            $attributeCode
                        )
                    );
                    $this->_redirect('catalog/*/edit', array('attribute_id' => $id, '_current' => true));
                    return;
                }
            }
            $data['attribute_code'] = $attributeCode;

            //validate frontend_input
            if (isset($data['frontend_input'])) {
                /** @var $inputType \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator */
                $inputType = $this->_objectManager->create(
                    'Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator'
                );
                if (!$inputType->isValid($data['frontend_input'])) {
                    foreach ($inputType->getMessages() as $message) {
                        $this->messageManager->addError($message);
                    }
                    $this->_redirect('catalog/*/edit', array('attribute_id' => $id, '_current' => true));
                    return;
                }
            }

            if ($id) {
                $model->load($id);
                if (!$model->getId()) {
                    $this->messageManager->addError(__('This attribute no longer exists.'));
                    $this->_redirect('catalog/*/');
                    return;
                }
                // entity type check
                if ($model->getEntityTypeId() != $this->_entityTypeId) {
                    $this->messageManager->addError(__('You can\'t update your attribute.'));
                    $session->setAttributeData($data);
                    $this->_redirect('catalog/*/');
                    return;
                }

                $data['attribute_code'] = $model->getAttributeCode();
                $data['is_user_defined'] = $model->getIsUserDefined();
                $data['frontend_input'] = $model->getFrontendInput();
            } else {
                /**
                 * @todo add to helper and specify all relations for properties
                 */
                $data['source_model'] = $helper->getAttributeSourceModelByInputType($data['frontend_input']);
                $data['backend_model'] = $helper->getAttributeBackendModelByInputType($data['frontend_input']);
            }

            $data += array('is_filterable' => 0, 'is_filterable_in_search' => 0, 'apply_to' => array());

            if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
                $data['backend_type'] = $model->getBackendTypeByInput($data['frontend_input']);
            }

            $defaultValueField = $model->getDefaultValueByInput($data['frontend_input']);
            if ($defaultValueField) {
                $data['default_value'] = $this->getRequest()->getParam($defaultValueField);
            }

            if (!$model->getIsUserDefined() && $model->getId()) {
                // Unset attribute field for system attributes
                unset($data['apply_to']);
            }

            $model->addData($data);

            if (!$id) {
                $model->setEntityTypeId($this->_entityTypeId);
                $model->setIsUserDefined(1);
            }

            $groupCode = $this->getRequest()->getParam('group');
            if ($this->getRequest()->getParam('set') && $groupCode) {
                // For creating product attribute on product page we need specify attribute set and group
                $attributeSetId = $isNewAttributeSet ? $attributeSet->getId() : $this->getRequest()->getParam('set');
                $groupCollection = $isNewAttributeSet ? $attributeSet->getGroups() : $this->_objectManager->create(
                    'Magento\Eav\Model\Resource\Entity\Attribute\Group\Collection'
                )->setAttributeSetFilter(
                    $attributeSetId
                )->load();
                foreach ($groupCollection as $group) {
                    if ($group->getAttributeGroupCode() == $groupCode) {
                        $attributeGroupId = $group->getAttributeGroupId();
                        break;
                    }
                }
                $model->setAttributeSetId($attributeSetId);
                $model->setAttributeGroupId($attributeGroupId);
            }

            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved the product attribute.'));

                $this->_attributeLabelCache->clean();
                $session->setAttributeData(false);
                if ($this->getRequest()->getParam('popup')) {
                    $requestParams = array(
                        'id' => $this->getRequest()->getParam('product'),
                        'attribute' => $model->getId(),
                        '_current' => true,
                        'product_tab' => $this->getRequest()->getParam('product_tab')
                    );
                    if ($isNewAttributeSet) {
                        $requestParams['new_attribute_set_id'] = $attributeSet->getId();
                    }
                    $this->_redirect('catalog/product/addAttribute', $requestParams);
                } elseif ($redirectBack) {
                    $this->_redirect('catalog/*/edit', array('attribute_id' => $model->getId(), '_current' => true));
                } else {
                    $this->_redirect('catalog/*/', array());
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $session->setAttributeData($data);
                $this->_redirect('catalog/*/edit', array('attribute_id' => $id, '_current' => true));
                return;
            }
        }
        $this->_redirect('catalog/*/');
    }

    /**
     * @return void
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        if ($id) {
            $model = $this->_objectManager->create('Magento\Catalog\Model\Resource\Eav\Attribute');

            // entity type check
            $model->load($id);
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                $this->messageManager->addError(__('This attribute cannot be deleted.'));
                $this->_redirect('catalog/*/');
                return;
            }

            try {
                $model->delete();
                $this->messageManager->addSuccess(__('The product attribute has been deleted.'));
                $this->_redirect('catalog/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect(
                    'catalog/*/edit',
                    array('attribute_id' => $this->getRequest()->getParam('attribute_id'))
                );
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find an attribute to delete.'));
        $this->_redirect('catalog/*/');
    }

    /**
     * ACL check
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::attributes_attributes');
    }
}
