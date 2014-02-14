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
 * @category    Magento
 * @package     Magento_Customer
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
     * @param ElementFactory $attrDataFactory
     */
    public function __construct(ElementFactory $attrDataFactory)
    {
        $this->_attrDataFactory = $attrDataFactory;
    }

    /**
     * Validate EAV model attributes with data models
     *
     * @param \Magento\Core\Model\AbstractModel $entity
     * @return bool
     */
    public function isValid($entity)
    {
        $data = array();
        if ($this->_data) {
            $data = $this->_data;
        } elseif ($entity instanceof \Magento\Object) {
            $data = $entity->getData();
        }
        return $this->validateData($data, $this->_attributes, $this->_entityType);
    }

    /**
     * @param array                                                    $data
     * @param \Magento\Customer\Service\V1\Dto\Eav\AttributeMetadata[] $attributes
     * @param string                                                   $entityType
     * @return bool
     */
    public function validateData($data, $attributes, $entityType)
    {
        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if (!$attribute->getDataModel() && !$attribute->getFrontendInput()) {
                continue;
            }
            if (!isset($data[$attributeCode])) {
                $data[$attributeCode] = null;
            }
            $dataModel = $this->_attrDataFactory->create(
                $attribute, $entityType, $data[$attributeCode]
            );
            $dataModel->setExtractedData($data);
            $result = $dataModel->validateValue($data[$attributeCode]);
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
