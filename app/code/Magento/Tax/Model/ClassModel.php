<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Tax\Api\Data\TaxClassInterface;

/**
 * Tax class model
 *
 * @method \Magento\Tax\Model\Resource\TaxClass _getResource()
 * @method \Magento\Tax\Model\Resource\TaxClass getResource()
 */
class ClassModel extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Tax\Api\Data\TaxClassInterface
{
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
     * @param \Magento\Framework\Api\MetadataServiceInterface $metadataService
     * @param AttributeValueFactory $customAttributeFactory
     * @param TaxClass\Factory $classFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\MetadataServiceInterface $metadataService,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Tax\Model\TaxClass\Factory $classFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $metadataService,
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
        $this->_init('Magento\Tax\Model\Resource\TaxClass');
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
            throw new CouldNotDeleteException('This class no longer exists.');
        }

        $typeModel = $this->_classFactory->create($this);

        if ($typeModel->getAssignedToRules()->getSize() > 0) {
            throw new CouldNotDeleteException(
                'You cannot delete this tax class because it is used in Tax Rules.' .
                ' You have to delete the rules it is used in first.'
            );
        }

        if ($typeModel->isAssignedToObjects()) {
            throw new CouldNotDeleteException(
                'You cannot delete this tax class because it is used in existing %object(s).',
                ['object' => $typeModel->getObjectTypeName()]
            );
        }

        return true;
    }

    /**
     * Validate tax class can be deleted
     *
     * @return $this
     * @throws \Magento\Framework\Model\Exception
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
        return $this->getData('class_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        return $this->getData('class_name');
    }

    /**
     * {@inheritdoc}
     */
    public function getClassType()
    {
        return $this->getData('class_type');
    }
    /**
     * Set tax class ID.
     *
     * @param int $classId
     * @return $this
     */
    public function setClassId($classId)
    {
        return $this->setData('class_id', $classId);
    }

    /**
     * Set tax class name.
     *
     * @param string $className
     * @return $this
     */
    public function setClassName($className)
    {
        return $this->setData('class_name', $className);
    }

    /**
     * Set tax class type.
     *
     * @param string $classType
     * @return $this
     */
    public function setClassType($classType)
    {
        return $this->setData('class_type', $classType);
    }
    //@codeCoverageIgnoreEnd
}
