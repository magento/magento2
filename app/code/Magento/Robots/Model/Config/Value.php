<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Robots\Model\Config;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreResolver;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Backend model for design/search_engine_robots/custom_instructions configuration value.
 *
 * Required to implement Page Cache functionality.
 *
 * @api
 * @since 100.1.0
 */
class Value extends ConfigValue implements IdentityInterface
{
    /**
     * Cache tag for robots.txt cached data
     */
    const CACHE_TAG = 'robots';

    /**
     * Model cache tag for clear cache in after save and after delete
     *
     * @var string
     * @since 100.1.0
     */
    protected $_cacheTag = true;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param StoreResolver $storeResolver
     * @param StoreManagerInterface|null $storeManager
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        StoreResolver $storeResolver,
        StoreManagerInterface $storeManager = null,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeManager = $storeManager ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(StoreManagerInterface::class);

        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Get unique page cache identities
     *
     * @return array
     * @since 100.1.0
     */
    public function getIdentities()
    {
        return [
            self::CACHE_TAG . '_' . $this->storeManager->getStore()->getId(),
        ];
    }
}
