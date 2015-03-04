<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Magento\Eav\Model\Entity;

/**
 * @codeCoverageIgnore
 */
class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Framework\App\Resource
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
     * @var \Magento\Eav\Model\Resource\Helper
     */
    protected $resourceHelper;

    /**
     * @var \Magento\Framework\Validator\UniversalFactory
     */
    protected $universalFactory;

    /**
     * @var \Magento\Framework\Model\Resource\Db\TransactionManagerInterface
     */
    protected $transactionManager;

    /**
     * @var \Magento\Framework\Model\Resource\Db\ObjectRelationProcessor
     */
    protected $objectRelationProcessor;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param Attribute\Set $attrSetEntity
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Eav\Model\Resource\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Framework\Model\Resource\Db\TransactionManagerInterface $transactionManager
     * @param \Magento\Framework\Model\Resource\Db\ObjectRelationProcessor $objectRelationProcessor
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\Attribute\Set $attrSetEntity,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Eav\Model\Resource\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\Model\Resource\Db\TransactionManagerInterface $transactionManager,
        \Magento\Framework\Model\Resource\Db\ObjectRelationProcessor $objectRelationProcessor
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
     * @return \Magento\Framework\App\Resource
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
     * @return \Magento\Eav\Model\Resource\Helper
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
     * @return \Magento\Framework\Model\Resource\Db\ObjectRelationProcessor
     */
    public function getObjectRelationProcessor()
    {
        return $this->objectRelationProcessor;
    }

    /**
     * @return \Magento\Framework\Model\Resource\Db\TransactionManagerInterface
     */
    public function getTransactionManager()
    {
        return $this->transactionManager;
    }
}
