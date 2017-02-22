<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Model\ResourceModel;

class Translate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb implements
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
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param string $connectionName
     * @param null|string $scope
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver,
        $connectionName = null,
        $scope = null
    ) {
        $this->scopeResolver = $scopeResolver;
        $this->scope = $scope;
        parent::__construct($context, $connectionName);
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
        if ($storeId === null) {
            $storeId = $this->getStoreId();
        }

        $connection = $this->getConnection();
        if (!$connection) {
            return [];
        }

        $select = $connection->select()
            ->from($this->getMainTable(), ['string', 'translate'])
            ->where('store_id IN (0 , :store_id)')
            ->where('locale = :locale')
            ->order('store_id');

        $bind = [':locale' => (string)$locale, ':store_id' => $storeId];

        return $connection->fetchPairs($select, $bind);
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
        if ($storeId === null) {
            $storeId = $this->getStoreId();
        }

        $connection = $this->getConnection();
        if (!$connection) {
            return [];
        }

        if (empty($strings)) {
            return [];
        }

        $bind = [':store_id' => $storeId];
        $select = $connection->select()
            ->from($this->getMainTable(), ['string', 'translate'])
            ->where('string IN (?)', $strings)
            ->where('store_id = :store_id');

        return $connection->fetchPairs($select, $bind);
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
