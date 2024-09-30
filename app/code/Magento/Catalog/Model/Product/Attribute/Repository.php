<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Product attribute repository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Repository implements \Magento\Catalog\Api\ProductAttributeRepositoryInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Attribute
     */
    protected $attributeResource;

    /**
     * @var \Magento\Eav\Model\AttributeRepository
     */
    protected $eavAttributeRepository;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory
     */
    protected $inputtypeValidatorFactory;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Attribute $attributeResource
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $validatorFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Attribute $attributeResource,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $validatorFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeResource = $attributeResource;
        $this->productHelper = $productHelper;
        $this->filterManager = $filterManager;
        $this->eavAttributeRepository = $eavAttributeRepository;
        $this->eavConfig = $eavConfig;
        $this->inputtypeValidatorFactory = $validatorFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function get($attributeCode)
    {
        return $this->eavAttributeRepository->get(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode
        );
    }

    /**
     * @inheritdoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        return $this->eavAttributeRepository->getList(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $searchCriteria
        );
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute)
    {
        $attribute->setEntityTypeId(
            $this->eavConfig
                ->getEntityType(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE)
                ->getId()
        );
        if ($attribute->getAttributeId()) {
            $existingModel = $this->get($attribute->getAttributeCode());

            if (!$existingModel->getAttributeId()) {
                throw NoSuchEntityException::singleField('attribute_code', $existingModel->getAttributeCode());
            }

            // Attribute code must not be changed after attribute creation
            $attribute->setAttributeCode($existingModel->getAttributeCode());
            $attribute->setAttributeId($existingModel->getAttributeId());
            $attribute->setIsUserDefined($existingModel->getIsUserDefined());
            $attribute->setFrontendInput($existingModel->getFrontendInput());
            $attribute->setBackendModel($existingModel->getBackendModel());

            $this->updateDefaultFrontendLabel($attribute, $existingModel);
        } else {
            $attribute->setAttributeId(null);

            if (!$attribute->getFrontendLabels() && !$attribute->getDefaultFrontendLabel()) {
                throw InputException::requiredField('frontend_label');
            }

            $frontendLabel = $this->updateDefaultFrontendLabel($attribute, null);

            $attribute->setAttributeCode(
                $attribute->getAttributeCode() ?: $this->generateCode($frontendLabel)
            );
            $this->validateCode($attribute->getAttributeCode());
            $this->validateFrontendInput($attribute->getFrontendInput());

            $attribute->setBackendType(
                $attribute->getBackendTypeByInput($attribute->getFrontendInput())
            );
            $attribute->setSourceModel(
                $this->productHelper->getAttributeSourceModelByInputType($attribute->getFrontendInput())
            );
            $attribute->setBackendModel(
                $this->productHelper->getAttributeBackendModelByInputType($attribute->getFrontendInput())
            );
            $attribute->setIsUserDefined(1);
        }
        if (!empty($attribute->getData(AttributeInterface::OPTIONS))) {
            $options = [];
            $sortOrder = 0;
            $default = [];
            $optionIndex = 0;
            foreach ($attribute->getOptions() as $option) {
                $optionIndex++;
                $optionId = $option->getValue() ?: 'option_' . $optionIndex;
                $options['value'][$optionId][0] = $option->getLabel();
                $options['order'][$optionId] = $option->getSortOrder() ?: $sortOrder++;
                if (is_array($option->getStoreLabels())) {
                    foreach ($option->getStoreLabels() as $label) {
                        $options['value'][$optionId][$label->getStoreId()] = $label->getLabel();
                    }
                }
                if ($option->getIsDefault()) {
                    $default[] = $optionId;
                }
            }
            $attribute->setDefault($default);
            if (count($options)) {
                $attribute->setOption($options);
            }
        }
        $this->attributeResource->save($attribute);
        return $this->get($attribute->getAttributeCode());
    }

    /**
     * @inheritdoc
     */
    public function delete(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute)
    {
        $this->attributeResource->delete($attribute);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($attributeCode)
    {
        $this->delete(
            $this->get($attributeCode)
        );
        return true;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getCustomAttributesMetadata($dataObjectClassName = null)
    {
        return $this->getList($this->searchCriteriaBuilder->create())->getItems();
    }

    /**
     * Generate code from label
     *
     * @param string $label
     * @return string
     */
    protected function generateCode($label)
    {
        $code = substr(
            preg_replace('/[^a-z_0-9]/', '_', $this->filterManager->translitUrl($label)),
            0,
            Attribute::ATTRIBUTE_CODE_MAX_LENGTH
        );
        $validatorAttrCode = new \Zend_Validate_Regex(['pattern' => '/^[a-z][a-z_0-9]{0,29}[a-z0-9]$/']);
        if (!$validatorAttrCode->isValid($code)) {
            $code = 'attr_' . ($code ?: substr(hash('sha256', time()), 0, 8));
        }

        return $code;
    }

    /**
     * Validate attribute code
     *
     * @param string $code
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function validateCode($code)
    {
        $validatorAttrCode = new \Zend_Validate_Regex(
            ['pattern' => '/^[a-z][a-z_0-9]{0,' . Attribute::ATTRIBUTE_CODE_MAX_LENGTH . '}$/']
        );
        if (!$validatorAttrCode->isValid($code)) {
            throw InputException::invalidFieldValue('attribute_code', $code);
        }
    }

    /**
     * Validate Frontend Input Type
     *
     * @param  string $frontendInput
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function validateFrontendInput($frontendInput)
    {
        /** @var \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator $validator */
        $validator = $this->inputtypeValidatorFactory->create();
        if (!$validator->isValid($frontendInput)) {
            throw InputException::invalidFieldValue('frontend_input', $frontendInput);
        }
    }

    /**
     * This method sets default frontend value using given default frontend value or frontend value from admin store
     * if default frontend value is not presented.
     * If both default frontend label and admin store frontend label are not given it throws exception
     * for attribute creation process or sets existing attribute value for attribute update action.
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface|null $existingModel
     * @return string|null
     * @throws InputException
     */
    private function updateDefaultFrontendLabel($attribute, $existingModel)
    {
        $frontendLabel = $attribute->getDefaultFrontendLabel();
        if (empty($frontendLabel)) {
            $frontendLabel = $this->extractAdminStoreFrontendLabel($attribute);
            if (empty($frontendLabel)) {
                if ($existingModel) {
                    $frontendLabel = $existingModel->getDefaultFrontendLabel();
                } else {
                    throw InputException::invalidFieldValue('frontend_label', null);
                }
            }
            $attribute->setDefaultFrontendLabel($frontendLabel);
        }
        return $frontendLabel;
    }

    /**
     * This method extracts frontend label from FrontendLabel object for admin store.
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @return string|null
     */
    private function extractAdminStoreFrontendLabel($attribute)
    {
        $frontendLabel = [];
        $frontendLabels = $attribute->getFrontendLabels();
        if (isset($frontendLabels[0])
            && $frontendLabels[0] instanceof \Magento\Eav\Api\Data\AttributeFrontendLabelInterface
        ) {
            foreach ($attribute->getFrontendLabels() as $label) {
                $frontendLabel[$label->getStoreId()] = $label->getLabel();
            }
        }
        return $frontendLabel[0] ?? null;
    }
}
