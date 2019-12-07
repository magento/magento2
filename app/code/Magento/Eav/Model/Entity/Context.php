<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity;

/**
 * Constructor modification point for Magento\Eav\Model\Entity.
 *
 * All context classes were introduced to allow for backwards compatible constructor modifications
 * of classes that were supposed to be extended by extension developers.
 *
 * Do not call methods of this class directly.
 *
 * As Magento moves from inheritance-based APIs all such classes will be deprecated together with
 * the classes they were introduced for.
 *
 * @api
 * @codeCoverageIgnore
 * @since 100.0.2
 */
class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var Attribute\Set
     */
    protected $attributeSetEntity;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $localeFormat;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Helper
     */
    protected $resourceHelper;

    /**
     * @var \Magento\Framework\Validator\UniversalFactory
     */
    protected $universalFactory;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface
     */
    protected $transactionManager;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor
     */
    protected $objectRelationProcessor;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param Attribute\Set $attrSetEntity
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Eav\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface $transactionManager
     * @param \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor $objectRelationProcessor
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\Attribute\Set $attrSetEntity,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface $transactionManager,
        \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor $objectRelationProcessor
    ) {
        $this->eavConfig = $eavConfig;
        $this->resource = $resource;
        $this->attributeSetEntity = $attrSetEntity;
        $this->localeFormat = $localeFormat;
        $this->resourceHelper = $resourceHelper;
        $this->universalFactory = $universalFactory;
        $this->transactionManager = $transactionManager;
        $this->objectRelationProcessor = $objectRelationProcessor;
    }

    /**
     * @return \Magento\Eav\Model\Config
     */
    public function getEavConfig()
    {
        return $this->eavConfig;
    }

    /**
     * @return \Magento\Framework\App\ResourceConnection
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return Attribute\Set
     */
    public function getAttributeSetEntity()
    {
        return $this->attributeSetEntity;
    }

    /**
     * @return \Magento\Framework\Locale\FormatInterface
     */
    public function getLocaleFormat()
    {
        return $this->localeFormat;
    }

    /**
     * @return \Magento\Eav\Model\ResourceModel\Helper
     */
    public function getResourceHelper()
    {
        return $this->resourceHelper;
    }

    /**
     * @return \Magento\Framework\Validator\UniversalFactory
     */
    public function getUniversalFactory()
    {
        return $this->universalFactory;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor
     */
    public function getObjectRelationProcessor()
    {
        return $this->objectRelationProcessor;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface
     */
    public function getTransactionManager()
    {
        return $this->transactionManager;
    }
}
