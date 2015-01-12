<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

class Repository implements \Magento\Catalog\Api\ProductAttributeRepositoryInterface
{
    /**
     * @var \Magento\Catalog\Model\Resource\Attribute
     */
    protected $attributeResource;

    /**
     * @var \Magento\Eav\Model\AttributeRepository
     */
    protected $eavAttributeRepository;

    /**
     * @var \Magento\Catalog\Api\Data\ProductAttributeInterfaceDataBuilder
     */
    protected $attributeBuilder;

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
     * @var \Magento\Framework\Api\Config\MetadataConfig
     */
    protected $metadataConfig;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaDataBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @param \Magento\Catalog\Model\Resource\Attribute $attributeResource
     * @param \Magento\Catalog\Api\Data\ProductAttributeDataBuilder $attributeBuilder
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $validatorFactory
     * @param \Magento\Framework\Api\Config\MetadataConfig $metadataConfig
     * @param \Magento\Framework\Api\SearchCriteriaDataBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\Attribute $attributeResource,
        \Magento\Catalog\Api\Data\ProductAttributeDataBuilder $attributeBuilder,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $validatorFactory,
        \Magento\Framework\Api\Config\MetadataConfig $metadataConfig,
        \Magento\Framework\Api\SearchCriteriaDataBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        $this->attributeResource = $attributeResource;
        $this->attributeBuilder = $attributeBuilder;
        $this->productHelper = $productHelper;
        $this->filterManager = $filterManager;
        $this->eavAttributeRepository = $eavAttributeRepository;
        $this->eavConfig = $eavConfig;
        $this->inputtypeValidatorFactory = $validatorFactory;
        $this->metadataConfig = $metadataConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function get($attributeCode)
    {
        return $this->eavAttributeRepository->get(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        return $this->eavAttributeRepository->getList(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $searchCriteria
        );
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute)
    {
        $this->attributeBuilder->populate($attribute);

        if ($attribute->getAttributeId()) {
            $existingModel = $this->get($attribute->getAttributeCode());

            if (!$existingModel->getAttributeId()) {
                throw NoSuchEntityException::singleField('attribute_code', $existingModel->getAttributeCode());
            }

            $this->attributeBuilder->setAttributeId($existingModel->getAttributeId());
            $this->attributeBuilder->setIsUserDefined($existingModel->getIsUserDefined());
            $this->attributeBuilder->setFrontendInput($existingModel->getFrontendInput());

            if (is_array($attribute->getFrontendLabels())) {
                $frontendLabel[0] = $existingModel->getDefaultFrontendLabel();
                foreach ($attribute->getFrontendLabels() as $item) {
                    $frontendLabel[$item->getStoreId()] = $item->getLabel();
                }
                $this->attributeBuilder->setDefaultFrontendLabel($frontendLabel);
            }
            if (!$attribute->getIsUserDefined()) {
                // Unset attribute field for system attributes
                $this->attributeBuilder->setApplyTo(null);
            }
        } else {
            $this->attributeBuilder->setAttributeId(null);

            if (!$attribute->getFrontendLabels() && !$attribute->getDefaultFrontendLabel()) {
                throw InputException::requiredField('frontend_label');
            }

            $frontendLabels = [];
            if ($attribute->getDefaultFrontendLabel()) {
                $frontendLabels[0] = $attribute->getDefaultFrontendLabel();
            }
            if ($attribute->getFrontendLabels() && is_array($attribute->getFrontendLabels())) {
                foreach ($attribute->getFrontendLabels() as $label) {
                    $frontendLabels[$label->getStoreId()] = $label->getLabel();
                }
                if (!isset($frontendLabels[0]) || !$frontendLabels[0]) {
                    throw InputException::invalidFieldValue('frontend_label', null);
                }

                $this->attributeBuilder->setDefaultFrontendLabel($frontendLabels);
            }
            $this->attributeBuilder->setAttributeCode(
                $attribute->getAttributeCode() ?: $this->generateCode($frontendLabels[0])
            );
            $this->validateCode($attribute->getAttributeCode());
            $this->validateFrontendInput($attribute->getFrontendInput());

            $this->attributeBuilder->setBackendType(
                $attribute->getBackendTypeByInput($attribute->getFrontendInput())
            );
            $this->attributeBuilder->setSourceModel(
                $this->productHelper->getAttributeSourceModelByInputType($attribute->getFrontendInput())
            );
            $this->attributeBuilder->setBackendModel(
                $this->productHelper->getAttributeBackendModelByInputType($attribute->getFrontendInput())
            );
            $this->attributeBuilder->setEntityTypeId(
                $this->eavConfig
                    ->getEntityType(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE)
                    ->getId()
            );
            $this->attributeBuilder->setIsUserDefined(1);
        }
        $attribute = $this->attributeBuilder->create();
        $this->attributeResource->save($attribute);
        return $attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute)
    {
        $this->attributeResource->delete($attribute);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($attributeCode)
    {
        $this->delete(
            $this->get($attributeCode)
        );
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomAttributesMetadata($dataObjectClassName = null)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            [
                $this->filterBuilder
                    ->setField('attribute_set_id')
                    ->setValue(\Magento\Catalog\Api\Data\ProductAttributeInterface::DEFAULT_ATTRIBUTE_SET_ID)
                    ->create(),
            ]
        );

        $customAttributes = [];
        $entityAttributes = $this->getList($searchCriteria->create())->getItems();

        foreach ($entityAttributes as $attributeMetadata) {
            $customAttributes[] = $attributeMetadata;
        }
        return array_merge($customAttributes, $this->metadataConfig->getCustomAttributesMetadata($dataObjectClassName));
    }

    /**
     * Generate code from label
     *
     * @param string $label
     * @return string
     */
    protected function generateCode($label)
    {
        $code = substr(preg_replace('/[^a-z_0-9]/', '_', $this->filterManager->translitUrl($label)), 0, 30);
        $validatorAttrCode = new \Zend_Validate_Regex(['pattern' => '/^[a-z][a-z_0-9]{0,29}[a-z0-9]$/']);
        if (!$validatorAttrCode->isValid($code)) {
            $code = 'attr_' . ($code ?: substr(md5(time()), 0, 8));
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
        $validatorAttrCode = new \Zend_Validate_Regex(['pattern' => '/^[a-z][a-z_0-9]{0,30}$/']);
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
}
