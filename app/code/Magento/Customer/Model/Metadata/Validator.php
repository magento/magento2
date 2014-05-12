<?php
/**
 * Attribute data validator
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
namespace Magento\Customer\Model\Metadata;

class Validator extends \Magento\Eav\Model\Validator\Attribute\Data
{
    /**
     * @var string
     */
    protected $_entityType;

    /**
     * @var array
     */
    protected $_entityData;

    /**
     * @param ElementFactory $attrDataFactory
     */
    public function __construct(ElementFactory $attrDataFactory)
    {
        $this->_attrDataFactory = $attrDataFactory;
    }

    /**
     * Validate EAV model attributes with data models
     *
     * @param \Magento\Framework\Object|array $entityData Data set from the Model attributes
     * @return bool
     */
    public function isValid($entityData)
    {
        if ($entityData instanceof \Magento\Framework\Object) {
            $this->_entityData = $entityData->getData();
        } else {
            $this->_entityData = $entityData;
        }
        return $this->validateData($this->_data, $this->_attributes, $this->_entityType);
    }

    /**
     * @param array                                                    $data
     * @param \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata[] $attributes
     * @param string                                                   $entityType
     * @return bool
     */
    public function validateData(array $data, array $attributes, $entityType)
    {
        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if (!$attribute->getDataModel() && !$attribute->getFrontendInput()) {
                continue;
            }
            if (!isset($data[$attributeCode])) {
                $data[$attributeCode] = null;
            }
            $dataModel = $this->_attrDataFactory->create($attribute, $data[$attributeCode], $entityType);
            $dataModel->setExtractedData($data);
            $value = empty($data[$attributeCode]) && isset(
                $this->_entityData[$attributeCode]
            ) ? $this->_entityData[$attributeCode] : $data[$attributeCode];
            $result = $dataModel->validateValue($value);
            if (true !== $result) {
                $this->_addErrorMessages($attributeCode, (array)$result);
            }
        }
        return count($this->_messages) == 0;
    }

    /**
     * Set type of the entity
     *
     * @param string $entityType
     * @return void
     */
    public function setEntityType($entityType)
    {
        $this->_entityType = $entityType;
    }
}
