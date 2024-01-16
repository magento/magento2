<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

/**
 * Class Context
 */
class CacheContext implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var array
     */
    protected $entities = [];

    /**
     * @var array
     */
    private $tags = [];

    /**
     * Register entity Ids
     *
     * @param string $cacheTag
     * @param array $ids
     * @return $this
     */
    public function registerEntities($cacheTag, $ids)
    {
        $this->entities[$cacheTag] = array_merge($this->getRegisteredEntity($cacheTag), $ids);
        return $this;
    }

    /**
     * Register entity tags
     *
     * @param array $cacheTags
     * @return $this
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
     */
    public function getIdentities()
    {
        $identities = [];
        foreach ($this->entities as $cacheTag => $ids) {
            foreach ($ids as $id) {
                $identities[$cacheTag . '_' . $id] = true;
            }
        }
        return array_merge(array_keys($identities), array_unique($this->tags));
    }

    /**
     * Clear context data
     */
    public function flush(): void
    {
        $this->tags = [];
        $this->entities = [];
    }
}
