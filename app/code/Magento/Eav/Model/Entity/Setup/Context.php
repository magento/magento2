<?php
/**
 * Eav setup context object
 *
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
namespace Magento\Eav\Model\Entity\Setup;

class Context extends \Magento\Core\Model\Resource\Setup\Context
{
    /**
     * @var PropertyMapperInterface
     */
    protected $attributeMapper;

    /**
     * @param \Magento\Logger $logger
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\App\Resource $resource
     * @param \Magento\Module\Dir\Reader $modulesReader
     * @param \Magento\Module\ModuleListInterface $moduleList
     * @param \Magento\Core\Model\Resource\Resource $resourceResource
     * @param \Magento\Core\Model\Resource\Setup\MigrationFactory $migrationFactory
     * @param \Magento\Core\Model\Resource\Theme\CollectionFactory $themeResourceFactory
     * @param \Magento\Core\Model\Theme\CollectionFactory $themeFactory
     * @param \Magento\Encryption\EncryptorInterface $encryptor
     * @param \Magento\App\Filesystem $filesystem
     * @param PropertyMapperInterface $attributeMapper
     */
    public function __construct(
        \Magento\Logger $logger,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\App\Resource $resource,
        \Magento\Module\Dir\Reader $modulesReader,
        \Magento\Module\ModuleListInterface $moduleList,
        \Magento\Core\Model\Resource\Resource $resourceResource,
        \Magento\Core\Model\Resource\Setup\MigrationFactory $migrationFactory,
        \Magento\Core\Model\Resource\Theme\CollectionFactory $themeResourceFactory,
        \Magento\Core\Model\Theme\CollectionFactory $themeFactory,
        \Magento\Encryption\EncryptorInterface $encryptor,
        \Magento\App\Filesystem $filesystem,
        PropertyMapperInterface $attributeMapper
    ) {
        $this->attributeMapper = $attributeMapper;
        parent::__construct(
            $logger, $eventManager, $resource, $modulesReader, $moduleList, $resourceResource,
            $migrationFactory, $themeResourceFactory, $themeFactory, $encryptor, $filesystem
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
