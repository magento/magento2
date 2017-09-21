<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
 * Class SwatchAttributeCodes for getting codes of swatch attributes.
 */
class SwatchAttributeCodes
{
    /**
     * @var string
     */
    private $cacheKey;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * Key is attribute_id, value is attribute_code
     *
     * @var array
     */
    private $swatchAttributeCodes;

    /**
     * @var array
     */
    private $cacheTags;

    /**
     * SwatchAttributeList constructor.
     *
     * @param CacheInterface $cache
     * @param ResourceConnection $resourceConnection
     * @param string $cacheKey
     * @param array $cacheTags
     */
    public function __construct(
        CacheInterface $cache,
        ResourceConnection $resourceConnection,
        $cacheKey,
        array $cacheTags
    ) {
        $this->cache = $cache;
        $this->resourceConnection = $resourceConnection;
        $this->cacheKey = $cacheKey;
        $this->cacheTags = $cacheTags;
    }

    /**
     * Returns list of known swatch attribute codes. Check cache and database.
     * Key is attribute_id, value is attribute_code
     *
     * @return array
     */
    public function getCodes()
    {
        if ($this->swatchAttributeCodes === null) {
            $swatchAttributeCodesCache = $this->cache->load($this->cacheKey);
            if (false === $swatchAttributeCodesCache) {
                $swatchAttributeCodes = $this->getSwatchAttributeCodes();
                $this->cache->save(json_encode($swatchAttributeCodes), $this->cacheKey, $this->cacheTags);
            } else {
                $swatchAttributeCodes = json_decode($swatchAttributeCodesCache, true);
            }
            $this->swatchAttributeCodes = $swatchAttributeCodes;
        }

        return $this->swatchAttributeCodes;
    }

    /**
     * Returns list of known swatch attributes.
     *
     * Returns a map of id and code for all EAV attributes with swatches
     *
     * @return array
     */
    private function getSwatchAttributeCodes()
    {
        $select = $this->resourceConnection->getConnection()->select()
            ->from(
                ['a' => $this->resourceConnection->getTableName('eav_attribute')],
                [
                    'attribute_id' => 'a.attribute_id',
                    'attribute_code' => 'a.attribute_code',
                ]
            )->where(
                'a.attribute_id IN (?)',
                new \Zend_Db_Expr($this->getAttributeIdsSelect())
            );
        $result = $this->resourceConnection->getConnection()->fetchPairs($select);
        return $result;
    }

    /**
     * Returns Select for attributes Ids.
     *
     * Builds a "Select" object which loads all EAV attributes that has "swatch" options
     *
     * @return Select
     */
    private function getAttributeIdsSelect()
    {
        return $this->resourceConnection->getConnection()->select()
            ->from(
                ['o' => $this->resourceConnection->getTableName('eav_attribute_option')],
                ['attribute_id' => 'o.attribute_id']
            )->join(
                ['s' => $this->resourceConnection->getTableName('eav_attribute_option_swatch')],
                'o.option_id = s.option_id',
                []
            );
    }
}
