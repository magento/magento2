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
namespace Magento\Customer\Service\V1\Data\Eav;

use Magento\Framework\Service\Data\AttributeMetadataBuilderInterface;
use Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder;
use Magento\Framework\Service\Data\AttributeValueBuilder;
use Magento\Framework\Service\Data\MetadataServiceInterface;

/**
 * Class AttributeMetadataBuilder
 */
class AttributeMetadataBuilder extends AbstractExtensibleObjectBuilder implements AttributeMetadataBuilderInterface
{
    /**
     * Option builder
     *
     * @var \Magento\Customer\Service\V1\Data\Eav\OptionBuilder
     */
    protected $_optionBuilder;

    /**
     * Validation rule builder
     *
     * @var \Magento\Customer\Service\V1\Data\Eav\ValidationRuleBuilder
     */
    protected $_validationRuleBuilder;

    /**
     * @param \Magento\Framework\Service\Data\ObjectFactory $objectFactory
     * @param AttributeValueBuilder $valueBuilder
     * @param MetadataServiceInterface $metadataService
     * @param OptionBuilder $optionBuilder
     * @param ValidationRuleBuilder $validationRuleBuilder
     */
    public function __construct(
        \Magento\Framework\Service\Data\ObjectFactory $objectFactory,
        AttributeValueBuilder $valueBuilder,
        MetadataServiceInterface $metadataService,
        \Magento\Customer\Service\V1\Data\Eav\OptionBuilder $optionBuilder,
        \Magento\Customer\Service\V1\Data\Eav\ValidationRuleBuilder $validationRuleBuilder
    ) {
        parent::__construct($objectFactory, $valueBuilder, $metadataService);
        $this->_optionBuilder = $optionBuilder;
        $this->_validationRuleBuilder = $validationRuleBuilder;
        $this->_data[AttributeMetadata::OPTIONS] = array();
        $this->_data[AttributeMetadata::VALIDATION_RULES] = array();
    }

    /**
     * Set attribute code
     *
     * @param string $attributeCode
     * @return $this
     */
    public function setAttributeCode($attributeCode)
    {
        return $this->_set(AttributeMetadata::ATTRIBUTE_CODE, $attributeCode);
    }

    /**
     * Set front end input
     *
     * @param string $frontendInput
     * @return $this
     */
    public function setFrontendInput($frontendInput)
    {
        return $this->_set(AttributeMetadata::FRONTEND_INPUT, $frontendInput);
    }

    /**
     * Set input filter
     *
     * @param string $inputFilter
     * @return $this
     */
    public function setInputFilter($inputFilter)
    {
        return $this->_set(AttributeMetadata::INPUT_FILTER, $inputFilter);
    }

    /**
     * Set store label
     *
     * @param string $storeLabel
     * @return $this
     */
    public function setStoreLabel($storeLabel)
    {
        return $this->_set(AttributeMetadata::STORE_LABEL, $storeLabel);
    }

    /**
     * Set validation rules
     *
     * @param \Magento\Customer\Service\V1\Data\Eav\ValidationRule[] $validationRules
     * @return $this
     */
    public function setValidationRules($validationRules)
    {
        return $this->_set(AttributeMetadata::VALIDATION_RULES, $validationRules);
    }

    /**
     * Set options
     *
     * @param \Magento\Customer\Service\V1\Data\Eav\Option[] $options
     * @return $this
     */
    public function setOptions($options)
    {
        return $this->_set(AttributeMetadata::OPTIONS, $options);
    }

    /**
     * Set visible
     *
     * @param bool $visible
     * @return $this
     */
    public function setVisible($visible)
    {
        return $this->_set(AttributeMetadata::VISIBLE, $visible);
    }

    /**
     * Set required
     *
     * @param bool $required
     * @return $this
     */
    public function setRequired($required)
    {
        return $this->_set(AttributeMetadata::REQUIRED, $required);
    }

    /**
     * Set multiline count
     *
     * @param int $count
     * @return $this
     */
    public function setMultilineCount($count)
    {
        return $this->_set(AttributeMetadata::MULTILINE_COUNT, $count);
    }

    /**
     * Set data model
     *
     * @param string $dataModel
     * @return $this
     */
    public function setDataModel($dataModel)
    {
        return $this->_set(AttributeMetadata::DATA_MODEL, $dataModel);
    }

    /**
     * Set frontend class
     *
     * @param string $frontendClass
     * @return $this
     */
    public function setFrontendClass($frontendClass)
    {
        return $this->_set(AttributeMetadata::FRONTEND_CLASS, $frontendClass);
    }

    /**
     * Set is user defined
     *
     * @param bool $isUserDefined
     * @return $this
     */
    public function setIsUserDefined($isUserDefined)
    {
        return $this->_set(AttributeMetadata::USER_DEFINED, $isUserDefined);
    }

    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        return $this->_set(AttributeMetadata::SORT_ORDER, $sortOrder);
    }

    /**
     * Set front end label
     *
     * @param string $frontendLabel
     * @return $this
     */
    public function setFrontendLabel($frontendLabel)
    {
        return $this->_set(AttributeMetadata::FRONTEND_LABEL, $frontendLabel);
    }

    /**
     * Set is system
     *
     * @param bool $isSystem
     * @return $this
     */
    public function setIsSystem($isSystem)
    {
        return $this->_set(AttributeMetadata::SYSTEM, $isSystem);
    }

    /**
     * Set note
     *
     * @param string $note
     * @return $this
     */
    public function setNote($note)
    {
        return $this->_set(AttributeMetadata::NOTE, $note);
    }

    /**
     * @param string $backendType
     * @return AttributeMetadataBuilder
     */
    public function setBackendType($backendType)
    {
        return $this->_set(AttributeMetadata::BACKEND_TYPE, $backendType);
    }

    /**
     * {@inheritdoc}
     */
    protected function _setDataValues(array $data)
    {
        if (array_key_exists(AttributeMetadata::OPTIONS, $data)) {
            $options = array();
            if (is_array($data[AttributeMetadata::OPTIONS])) {
                foreach ($data[AttributeMetadata::OPTIONS] as $key => $option) {
                    $options[$key] = $this->_optionBuilder->populateWithArray($option)->create();
                }
            }
            $validationRules = array();
            if (is_array($data[AttributeMetadata::VALIDATION_RULES])) {
                foreach ($data[AttributeMetadata::VALIDATION_RULES] as $key => $value) {
                    $validationRules[$key] = $this->_validationRuleBuilder->populateWithArray($value)->create();
                }
            }

            $data[AttributeMetadata::OPTIONS] = $options;
            $data[AttributeMetadata::VALIDATION_RULES] = $validationRules;
        }

        return parent::_setDataValues($data);
    }
}
