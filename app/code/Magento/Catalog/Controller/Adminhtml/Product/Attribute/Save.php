<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Controller\Adminhtml\Product\Attribute;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Attribute;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\Product\Attribute\Frontend\Inputtype\Presentation;
use Magento\Framework\Serialize\Serializer\FormData;
use Magento\Catalog\Model\Product\AttributeSet\BuildFactory;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator;
use Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * Product attribute save controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends Attribute implements HttpPostActionInterface
{
    /**
     * @var BuildFactory
     */
    protected $buildFactory;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @var Product
     */
    protected $productHelper;

    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @var CollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var Presentation
     */
    private $presentation;

    /**
     * @var FormData|null
     */
    private $formDataSerializer;

    /**
     * @param Context $context
     * @param FrontendInterface $attributeLabelCache
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param BuildFactory $buildFactory
     * @param AttributeFactory $attributeFactory
     * @param ValidatorFactory $validatorFactory
     * @param CollectionFactory $groupCollectionFactory
     * @param FilterManager $filterManager
     * @param Product $productHelper
     * @param LayoutFactory $layoutFactory
     * @param Presentation|null $presentation
     * @param FormData|null $formDataSerializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        FrontendInterface $attributeLabelCache,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        BuildFactory $buildFactory,
        AttributeFactory $attributeFactory,
        ValidatorFactory $validatorFactory,
        CollectionFactory $groupCollectionFactory,
        FilterManager $filterManager,
        Product $productHelper,
        LayoutFactory $layoutFactory,
        Presentation $presentation = null,
        FormData $formDataSerializer = null
    ) {
        parent::__construct($context, $attributeLabelCache, $coreRegistry, $resultPageFactory);
        $this->buildFactory = $buildFactory;
        $this->filterManager = $filterManager;
        $this->productHelper = $productHelper;
        $this->attributeFactory = $attributeFactory;
        $this->validatorFactory = $validatorFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->layoutFactory = $layoutFactory;
        $this->presentation = $presentation ?: ObjectManager::getInstance()->get(Presentation::class);
        $this->formDataSerializer = $formDataSerializer
            ?: ObjectManager::getInstance()->get(FormData::class);
    }

    /**
     * @inheritdoc
     *
     * @return Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws \Zend_Validate_Exception
     */
    public function execute()
    {
        try {
            $optionData = $this->formDataSerializer
                ->unserialize($this->getRequest()->getParam('serialized_options', '[]'));
        } catch (\InvalidArgumentException $e) {
            $message = __("The attribute couldn't be saved due to an error. Verify your information and try again. "
                . "If the error persists, please try again later.");
            $this->messageManager->addErrorMessage($message);
            return $this->returnResult('catalog/*/edit', ['_current' => true], ['error' => true]);
        }

        $data = $this->getRequest()->getPostValue();
        $data = array_replace_recursive(
            $data,
            $optionData
        );

        if ($data) {
            $setId = $this->getRequest()->getParam('set');

            $attributeSet = null;
            if (!empty($data['new_attribute_set_name'])) {
                $name = $this->filterManager->stripTags($data['new_attribute_set_name']);
                $name = trim($name);

                try {
                    /** @var Set $attributeSet */
                    $attributeSet = $this->buildFactory->create()
                        ->setEntityTypeId($this->_entityTypeId)
                        ->setSkeletonId($setId)
                        ->setName($name)
                        ->getAttributeSet();
                } catch (AlreadyExistsException $alreadyExists) {
                    $this->messageManager->addErrorMessage(__('An attribute set named \'%1\' already exists.', $name));
                    $this->_session->setAttributeData($data);
                    return $this->returnResult('catalog/*/edit', ['_current' => true], ['error' => true]);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage(
                        $e,
                        __('Something went wrong while saving the attribute.')
                    );
                }
            }

            $attributeId = $this->getRequest()->getParam('attribute_id');

            /** @var ProductAttributeInterface $model */
            $model = $this->attributeFactory->create();
            if ($attributeId) {
                $model->load($attributeId);
            }
            $attributeCode = $model && $model->getId()
                ? $model->getAttributeCode()
                : $this->getRequest()->getParam('attribute_code');
            $attributeCode = $attributeCode ?: $this->generateCode($this->getRequest()->getParam('frontend_label')[0]);
            if (strlen($attributeCode) > 0) {
                $validatorAttrCode = new \Zend_Validate_Regex(
                    ['pattern' => '/^[a-zA-Z\x{600}-\x{6FF}][a-zA-Z\x{600}-\x{6FF}_0-9]{0,30}$/u']
                );
                if (!$validatorAttrCode->isValid($attributeCode)) {
                    $this->messageManager->addErrorMessage(
                        __(
                            'Attribute code "%1" is invalid. Please use only letters (a-z or A-Z), ' .
                            'numbers (0-9) or underscore(_) in this field, first character should be a letter.',
                            $attributeCode
                        )
                    );
                    return $this->returnResult(
                        'catalog/*/edit',
                        ['attribute_id' => $attributeId, '_current' => true],
                        ['error' => true]
                    );
                }
            }
            $data['attribute_code'] = $attributeCode;

            //validate frontend_input
            if (isset($data['frontend_input'])) {
                /** @var Validator $inputType */
                $inputType = $this->validatorFactory->create();
                if (!$inputType->isValid($data['frontend_input'])) {
                    foreach ($inputType->getMessages() as $message) {
                        $this->messageManager->addErrorMessage($message);
                    }
                    return $this->returnResult(
                        'catalog/*/edit',
                        ['attribute_id' => $attributeId, '_current' => true],
                        ['error' => true]
                    );
                }
            }

            $data = $this->presentation->convertPresentationDataToInputType($data);

            if ($attributeId) {
                if (!$model->getId()) {
                    $this->messageManager->addErrorMessage(__('This attribute no longer exists.'));
                    return $this->returnResult('catalog/*/', [], ['error' => true]);
                }
                // entity type check
                if ($model->getEntityTypeId() != $this->_entityTypeId) {
                    $this->messageManager->addErrorMessage(__('We can\'t update the attribute.'));
                    $this->_session->setAttributeData($data);
                    return $this->returnResult('catalog/*/', [], ['error' => true]);
                }

                $data['attribute_code'] = $model->getAttributeCode();
                $data['is_user_defined'] = $model->getIsUserDefined();
                $data['frontend_input'] = $data['frontend_input'] ?? $model->getFrontendInput();
            } else {
                /**
                 * @todo add to helper and specify all relations for properties
                 */
                $data['source_model'] = $this->productHelper->getAttributeSourceModelByInputType(
                    $data['frontend_input']
                );
                $data['backend_model'] = $this->productHelper->getAttributeBackendModelByInputType(
                    $data['frontend_input']
                );

                if ($model->getIsUserDefined() === null) {
                    $data['backend_type'] = $model->getBackendTypeByInput($data['frontend_input']);
                }
            }

            $data += ['is_filterable' => 0, 'is_filterable_in_search' => 0];

            $defaultValueField = $model->getDefaultValueByInput($data['frontend_input']);
            if ($defaultValueField) {
                $data['default_value'] = $this->getRequest()->getParam($defaultValueField);
            }

            if (!$model->getIsUserDefined() && $model->getId()) {
                // Unset attribute field for system attributes
                unset($data['apply_to']);
            }

            $model->addData($data);

            if (!$attributeId) {
                $model->setEntityTypeId($this->_entityTypeId);
                $model->setIsUserDefined(1);
            }

            $groupCode = $this->getRequest()->getParam('group');
            if ($setId && $groupCode) {
                // For creating product attribute on product page we need specify attribute set and group
                $attributeSetId = $attributeSet ? $attributeSet->getId() : $setId;
                $groupCollection = $this->groupCollectionFactory->create()
                    ->setAttributeSetFilter($attributeSetId)
                    ->addFieldToFilter('attribute_group_code', $groupCode)
                    ->setPageSize(1)
                    ->load();

                $group = $groupCollection->getFirstItem();
                if (!$group->getId()) {
                    $group->setAttributeGroupCode($groupCode);
                    $group->setSortOrder($this->getRequest()->getParam('groupSortOrder'));
                    $group->setAttributeGroupName($this->getRequest()->getParam('groupName'));
                    $group->setAttributeSetId($attributeSetId);
                    $group->save();
                }

                $model->setAttributeSetId($attributeSetId);
                $model->setAttributeGroupId($group->getId());
            }

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the product attribute.'));

                $this->_attributeLabelCache->clean();
                $this->_session->setAttributeData(false);
                if ($this->getRequest()->getParam('popup')) {
                    $requestParams = [
                        'attributeId' => $this->getRequest()->getParam('product'),
                        'attribute' => $model->getId(),
                        '_current' => true,
                        'product_tab' => $this->getRequest()->getParam('product_tab'),
                    ];
                    if ($attributeSet !== null) {
                        $requestParams['new_attribute_set_id'] = $attributeSet->getId();
                    }
                    return $this->returnResult('catalog/product/addAttribute', $requestParams, ['error' => false]);
                } elseif ($this->getRequest()->getParam('back', false)) {
                    return $this->returnResult(
                        'catalog/*/edit',
                        ['attribute_id' => $model->getId(), '_current' => true],
                        ['error' => false]
                    );
                }
                return $this->returnResult('catalog/*/', [], ['error' => false]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_session->setAttributeData($data);
                return $this->returnResult(
                    'catalog/*/edit',
                    ['attribute_id' => $attributeId, '_current' => true],
                    ['error' => true]
                );
            }
        }
        return $this->returnResult('catalog/*/', [], ['error' => true]);
    }

    /**
     * Provides an initialized Result object.
     *
     * @param string $path
     * @param array $params
     * @param array $response
     * @return Json|Redirect
     */
    private function returnResult($path = '', array $params = [], array $response = [])
    {
        if ($this->isAjax()) {
            $layout = $this->layoutFactory->create();
            $layout->initMessages();

            $response['messages'] = [$layout->getMessagesBlock()->getGroupedHtml()];
            $response['params'] = $params;
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($response);
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath($path, $params);
    }

    /**
     * Define whether request is Ajax
     *
     * @return boolean
     */
    private function isAjax()
    {
        return $this->getRequest()->getParam('isAjax');
    }
}
