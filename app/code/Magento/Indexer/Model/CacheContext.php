<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Indexer\Model;

/**
 * Class Context
 */
class CacheContext implements \Magento\Framework\Object\IdentityInterface
{
    /**
     * @var array
     */
    protected $entities = [];

    /**
     * Register entity Ids
     *
     * @param string $cacheTag
     * @param array $ids
     * @return $this
     */
    public function registerEntities($cacheTag, $ids)
    {
        $this->entities[$cacheTag] =
            array_merge($this->getRegisteredEntity($cacheTag), $ids);
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
                $identities[] = $cacheTag . '_' . $id;
            }
        }
        return $identities;
    }
}
