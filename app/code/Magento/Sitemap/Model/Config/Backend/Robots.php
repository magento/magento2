<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Robots\Model\Config\Value as RobotsValue;
use Magento\Store\Model\StoreResolver;

/**
 * Backend model for sitemap/search_engines/submission_robots configuration value.
 * Required to implement Page Cache functionality.
 * @since 2.2.0
 */
class Robots extends Value implements IdentityInterface
{
    /**
     * Model cache tag for clear cache in after save and after delete
     *
     * @var string
     * @since 2.2.0
     */
    protected $_cacheTag = true;

    /**
     * @var StoreResolver
     * @since 2.2.0
     */
    private $storeResolver;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param StoreResolver $storeResolver
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @since 2.2.0
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        StoreResolver $storeResolver,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeResolver = $storeResolver;

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
     * @since 2.2.0
     */
    public function getIdentities()
    {
        return [
            RobotsValue::CACHE_TAG . '_' . $this->storeResolver->getCurrentStoreId(),
        ];
    }
}
