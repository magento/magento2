<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Exception\CouldNotDeleteException;

/**
 * Tax class model
 */
class ClassModel extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Tax\Api\Data\TaxClassInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_ID   = 'class_id';
    const KEY_NAME = 'class_name';
    const KEY_TYPE = 'class_type';
    /**#@-*/

    /**
     * Defines Customer Tax Class string
     */
    const TAX_CLASS_TYPE_CUSTOMER = 'CUSTOMER';

    /**
     * Defines Product Tax Class string
     */
    const TAX_CLASS_TYPE_PRODUCT = 'PRODUCT';

    /**
     * @var \Magento\Tax\Model\TaxClass\Factory
     */
    protected $_classFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param TaxClass\Factory $classFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Tax\Model\TaxClass\Factory $classFactory,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_classFactory = $classFactory;
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(\Magento\Tax\Model\ResourceModel\TaxClass::class);
    }

    /**
     * Check whether this class can be deleted
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    protected function checkClassCanBeDeleted()
    {
        if (!$this->getId()) {
            throw new CouldNotDeleteException(__('This class no longer exists.'));
        }

        $typeModel = $this->_classFactory->create($this);

        if ($typeModel->getAssignedToRules()->getSize() > 0) {
            throw new CouldNotDeleteException(
                __(
                    'You cannot delete this tax class because it is used in Tax Rules.'
                    . ' You have to delete the rules it is used in first.'
                )
            );
        }

        if ($typeModel->isAssignedToObjects()) {
            throw new CouldNotDeleteException(
                __(
                    'You cannot delete this tax class because it is used in existing %1(s).',
                    $typeModel->getObjectTypeName()
                )
            );
        }

        return true;
    }

    /**
     * Validate tax class can be deleted
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeDelete()
    {
        $this->checkClassCanBeDeleted();
        return parent::beforeDelete();
    }

    /**
     * @codeCoverageIgnoreStart
     * {@inheritdoc}
     */
    public function getClassId()
    {
        return $this->getData(self::KEY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        return $this->getData(self::KEY_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassType()
    {
        return $this->getData(self::KEY_TYPE);
    }

    /**
     * Set tax class ID.
     *
     * @param int $classId
     * @return $this
     */
    public function setClassId($classId)
    {
        return $this->setData(self::KEY_ID, $classId);
    }

    /**
     * Set tax class name.
     *
     * @param string $className
     * @return $this
     */
    public function setClassName($className)
    {
        return $this->setData(self::KEY_NAME, $className);
    }

    /**
     * Set tax class type.
     *
     * @param string $classType
     * @return $this
     */
    public function setClassType($classType)
    {
        return $this->setData(self::KEY_TYPE, $classType);
    }

    //@codeCoverageIgnoreEnd

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Tax\Api\Data\TaxClassExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Tax\Api\Data\TaxClassExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\TaxClassExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
