<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

/**
 * Class Context
 * @since 2.0.0
 */
class CacheContext implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $entities = [];

    /**
     * @var array
     * @since 2.1.0
     */
    private $tags = [];

    /**
     * Register entity Ids
     *
     * @param string $cacheTag
     * @param array $ids
     * @return $this
     * @since 2.0.0
     */
    public function registerEntities($cacheTag, $ids)
    {
        $this->entities[$cacheTag] =
            array_merge($this->getRegisteredEntity($cacheTag), $ids);
        return $this;
    }

    /**
     * Register entity tags
     *
     * @param string $cacheTag
     * @return $this
     * @since 2.1.0
     */
    public function registerTags($cacheTags)
    {
        $this->tags = array_merge($this->tags, $cacheTags);
        return $this;
    }

    /**
     * Returns registered entities
     *
     * @param string $cacheTag
     * @return array
     * @since 2.0.0
     */
    public function getRegisteredEntity($cacheTag)
    {
        if (empty($this->entities[$cacheTag])) {
            return [];
        } else {
            return $this->entities[$cacheTag];
        }
    }

    /**
     * Returns identities
     *
     * @return array
     * @since 2.0.0
     */
    public function getIdentities()
    {
        $identities = [];
        foreach ($this->entities as $cacheTag => $ids) {
            foreach ($ids as $id) {
                $identities[] = $cacheTag . '_' . $id;
            }
        }
        return array_merge($identities, array_unique($this->tags));
    }
}
