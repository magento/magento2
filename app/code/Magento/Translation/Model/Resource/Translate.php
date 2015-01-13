<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Model\Resource;

class Translate extends \Magento\Framework\Model\Resource\Db\AbstractDb implements
    \Magento\Framework\Translate\ResourceInterface
{
    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * @var null|string
     */
    protected $scope;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param null|string $scope
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver,
        $scope = null
    ) {
        $this->scopeResolver = $scopeResolver;
        $this->scope = $scope;
        parent::__construct($resource);
    }

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('translation', 'key_id');
    }

    /**
     * Retrieve translation array for store / locale code
     *
     * @param int $storeId
     * @param string $locale
     * @return array
     */
    public function getTranslationArray($storeId = null, $locale = null)
    {
        if (is_null($storeId)) {
            $storeId = $this->getStoreId();
        }

        $adapter = $this->_getReadAdapter();
        if (!$adapter) {
            return [];
        }

        $select = $adapter->select()
            ->from($this->getMainTable(), ['string', 'translate'])
            ->where('store_id IN (0 , :store_id)')
            ->where('locale = :locale')
            ->order('store_id');

        $bind = [':locale' => (string)$locale, ':store_id' => $storeId];

        return $adapter->fetchPairs($select, $bind);
    }

    /**
     * Retrieve translations array by strings
     *
     * @param array $strings
     * @param int|null $storeId
     * @return array
     */
    public function getTranslationArrayByStrings(array $strings, $storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = $this->getStoreId();
        }

        $adapter = $this->_getReadAdapter();
        if (!$adapter) {
            return [];
        }

        if (empty($strings)) {
            return [];
        }

        $bind = [':store_id' => $storeId];
        $select = $adapter->select()
            ->from($this->getMainTable(), ['string', 'translate'])
            ->where('string IN (?)', $strings)
            ->where('store_id = :store_id');

        return $adapter->fetchPairs($select, $bind);
    }

    /**
     * Retrieve table checksum
     *
     * @return int
     */
    public function getMainChecksum()
    {
        return $this->getChecksum($this->getMainTable());
    }

    /**
     * Retrieve current store identifier
     *
     * @return int
     */
    protected function getStoreId()
    {
        return $this->scopeResolver->getScope($this->scope)->getId();
    }
}
