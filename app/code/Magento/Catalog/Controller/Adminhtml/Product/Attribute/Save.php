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
namespace Magento\Catalog\Controller\Adminhtml\Product\Attribute;

class Save extends \Magento\Catalog\Controller\Adminhtml\Product\Attribute
{
    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Cache\FrontendInterface $attributeLabelCache
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Cache\FrontendInterface $attributeLabelCache,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
    ) {
        parent::__construct($context, $attributeLabelCache, $coreRegistry, $resultPageFactory);
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost();
        $resultRedirect = $this->resultRedirectFactory->create();
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
                    return $resultRedirect->setPath('catalog/*/edit', ['_current' => true]);
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
                    return $resultRedirect->setPath('catalog/*/edit', ['attribute_id' => $id, '_current' => true]);
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
                    return $resultRedirect->setPath('catalog/*/edit', ['attribute_id' => $id, '_current' => true]);
                }
            }

            if ($id) {
                $model->load($id);
                if (!$model->getId()) {
                    $this->messageManager->addError(__('This attribute no longer exists.'));
                    return $resultRedirect->setPath('catalog/*/');
                }
                // entity type check
                if ($model->getEntityTypeId() != $this->_entityTypeId) {
                    $this->messageManager->addError(__('You can\'t update your attribute.'));
                    $session->setAttributeData($data);
                    return $resultRedirect->setPath('catalog/*/');
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
                    $resultRedirect->setPath('catalog/product/addAttribute', $requestParams);
                } elseif ($redirectBack) {
                    $resultRedirect->setPath('catalog/*/edit', ['attribute_id' => $model->getId(), '_current' => true]);
                } else {
                    $resultRedirect->setPath('catalog/*/');
                }
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $session->setAttributeData($data);
                return $resultRedirect->setPath('catalog/*/edit', ['attribute_id' => $id, '_current' => true]);
            }
        }
        return $resultRedirect->setPath('catalog/*/');
    }
}
