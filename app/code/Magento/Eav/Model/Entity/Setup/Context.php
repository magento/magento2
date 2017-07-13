<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Setup;

/**
 * Constructor modification point for Magento\Eav\Model\Entity\Setup.
 *
 * All such context classes were introduced to allow for backwards compatible constructor modifications
 * of classes that were supposed to be extended by extension developers.
 *
 * Do not call methods of this class directly.
 *
 * As Magento moves from inheritance-based APIs all such classes will be deprecated together with their
 * corresponding abstract classes.
 *
 * @api
 * @codeCoverageIgnore
 */
class Context extends \Magento\Framework\Module\Setup\Context
{
    /**
     * @var PropertyMapperInterface
     */
    protected $attributeMapper;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\ResourceConnection $appResource
     * @param \Magento\Framework\Module\Dir\Reader $modulesReader
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Module\ResourceInterface $resource
     * @param \Magento\Framework\Module\Setup\MigrationFactory $migrationFactory
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Filesystem $filesystem
     * @param PropertyMapperInterface $attributeMapper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\ResourceConnection $appResource,
        \Magento\Framework\Module\Dir\Reader $modulesReader,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Module\ResourceInterface $resource,
        \Magento\Framework\Module\Setup\MigrationFactory $migrationFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Filesystem $filesystem,
        PropertyMapperInterface $attributeMapper
    ) {
        $this->attributeMapper = $attributeMapper;
        parent::__construct(
            $logger,
            $eventManager,
            $appResource,
            $modulesReader,
            $moduleList,
            $resource,
            $migrationFactory,
            $encryptor,
            $filesystem
        );
    }

    /**
     * @return PropertyMapperInterface
     */
    public function getAttributeMapper()
    {
        return $this->attributeMapper;
    }
}
