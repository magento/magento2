<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Product\Attribute;

use Laminas\Validator\Regex;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ResourceModel\Attribute as AttributeResource;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Validator\Attribute\Code;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filter\FilterManager;

/**
 * Product attribute repository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Repository implements \Magento\Catalog\Api\ProductAttributeRepositoryInterface
{
    /**
     * @var ValidatorFactory
     * @deprecated
     * @see $validatorFactory
     */
    protected $inputtypeValidatorFactory;

    /**
     * @var FilterableAllowedInputTypes
     */
    private FilterableAllowedInputTypes $filterableAllowedInputTypes;

    /**
     * @param AttributeResource $attributeResource
     * @param Product $productHelper
     * @param FilterManager $filterManager
     * @param AttributeRepositoryInterface $eavAttributeRepository
     * @param Config $eavConfig
     * @param ValidatorFactory $validatorFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Code $attributeCodeValidator
     * @param FilterableAllowedInputTypes|null $filterableAllowedInputTypes
     */
    public function __construct(
        protected AttributeResource $attributeResource,
        protected Product $productHelper,
        protected FilterManager $filterManager,
        protected AttributeRepositoryInterface $eavAttributeRepository,
        protected Config $eavConfig,
        protected ValidatorFactory $validatorFactory,
        protected SearchCriteriaBuilder $searchCriteriaBuilder,
        protected Code $attributeCodeValidator,
        ?FilterableAllowedInputTypes $filterableAllowedInputTypes = null
    ) {
        $this->inputtypeValidatorFactory = $validatorFactory;
        $this->filterableAllowedInputTypes = $filterableAllowedInputTypes
            ?? ObjectManager::getInstance()->get(FilterableAllowedInputTypes::class);
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function save(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute)
    {
        if (!$this->filterableAllowedInputTypes->isAllowed($attribute->getFrontendInput())) {
            if ($attribute->getIsFilterable()) {
                throw InputException::invalidFieldValue(
                    EavAttributeInterface::IS_FILTERABLE,
                    $attribute->getIsFilterable()
                );
            }

            if ($attribute->getIsFilterableInSearch()) {
                throw InputException::invalidFieldValue(
                    EavAttributeInterface::IS_FILTERABLE_IN_SEARCH,
                    $attribute->getIsFilterableInSearch()
                );
            }
        }

        $attribute->setEntityTypeId(
            $this->eavConfig
                ->getEntityType(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE)
                ->getId()
        );
        if (!$attribute->getAttributeId() && $attribute->getAttributeCode()) {
            try {
                $existingAttribute = $this->get($attribute->getAttributeCode());
                if ($existingAttribute->getAttributeId()) {
                    $attribute->setAttributeId($existingAttribute->getAttributeId());
                }
            } catch (NoSuchEntityException $e) {// phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
                // It's a new attribute, proceed as usual
            }
        }

        $validOptionIds = [];
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

            $source = $existingModel->getSource();
            if ($source) {
                foreach ($source->getAllOptions() as $opt) {
                    $validOptionIds[] = $opt['value'];
                }
            }
        } else {
            $attribute->setAttributeId(null);

            if (!$attribute->getFrontendLabels() && !$attribute->getDefaultFrontendLabel()) {
                throw InputException::requiredField('frontend_label');
            }

            $frontendLabel = $this->updateDefaultFrontendLabel($attribute, null);

            $this->validateFrontendInput($attribute->getFrontendInput());

            $attribute->setAttributeCode(
                $attribute->getAttributeCode() ?: $this->generateCode($frontendLabel)
            );
            $this->validateCode($attribute->getAttributeCode());

            $backendType = $attribute->getBackendTypeByInput($attribute->getFrontendInput());
            if ($attribute->getBackendType() && $attribute->getBackendType() !== $backendType) {
                throw InputException::invalidFieldValue('backend_type', $attribute->getBackendType());
            }
            $attribute->setBackendType($backendType);
            $attribute->setSourceModel(
                $this->productHelper->getAttributeSourceModelByInputType($attribute->getFrontendInput())
            );
            $attribute->setBackendModel(
                $this->productHelper->getAttributeBackendModelByInputType($attribute->getFrontendInput())
            );
            $attribute->setIsUserDefined(1);
        }
        if (!empty($attribute->getData(EavAttributeInterface::OPTIONS))) {
            $options = [];
            $sortOrder = 0;
            $default = [];
            $optionIndex = 0;
            foreach ($attribute->getOptions() as $option) {
                $optionIndex++;
                $optionId = $option->getValue();
                if ($optionId && is_numeric($optionId) && !in_array($optionId, $validOptionIds)) {
                    $optionId = null;
                }
                $optionId = $optionId ?: 'option_' . $optionIndex;
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

        $validatorAttrCode = new Regex(['pattern' => '/^[a-z][a-z_0-9]{0,29}[a-z0-9]$/']);
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
     * @throws InputException
     */
    protected function validateCode($code)
    {
        $isValid = $this->attributeCodeValidator->isValid($code);
        if (!$isValid) {
            throw InputException::invalidFieldValue('attribute_code', $code);
        }
    }

    /**
     * Validate Frontend Input Type
     *
     * @param  string $frontendInput
     * @return void
     * @throws InputException
     */
    protected function validateFrontendInput($frontendInput)
    {
        /** @var \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator $validator */
        $validator = $this->validatorFactory->create();
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
