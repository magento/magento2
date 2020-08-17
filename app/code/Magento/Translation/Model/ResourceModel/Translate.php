<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Model\ResourceModel;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Config;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Translate\ResourceInterface;
use Magento\Translation\App\Config\Type\Translation;

/**
 * Translate data resource model
 */
class Translate extends AbstractDb implements ResourceInterface
{
    /**
     * @var ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * @var Config
     */
    private $appConfig;

    /**
     * @var DeploymentConfig
     */
    private $deployedConfig;

    /**
     * @var null|string
     */
    protected $scope;

    /**
     * @param Context $context
     * @param ScopeResolverInterface $scopeResolver
     * @param string $connectionName
     * @param null|string $scope
     * @param Config|null $appConfig
     * @param DeploymentConfig|null $deployedConfig
     */
    public function __construct(
        Context $context,
        ScopeResolverInterface $scopeResolver,
        $connectionName = null,
        $scope = null,
        ?Config $appConfig = null,
        ?DeploymentConfig $deployedConfig = null
    ) {
        $this->scopeResolver = $scopeResolver;
        $this->scope = $scope;
        $this->appConfig = $appConfig ?? ObjectManager::getInstance()->get(Config::class);
        $this->deployedConfig = $deployedConfig ?? ObjectManager::getInstance()->get(DeploymentConfig::class);
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
     *
     * @return array
     */
    public function getTranslationArray($storeId = null, $locale = null)
    {
        if ($storeId === null) {
            $storeId = $this->getStoreId();
        }
        $locale = (string) $locale;

        $data = $this->appConfig->get(
            Translation::CONFIG_TYPE,
            $locale . '/' . $this->getStoreCode($storeId),
            []
        );
        $connection = $this->getConnection();
        if ($connection) {
            $select = $connection->select()
                ->from($this->getMainTable(), ['string', 'translate'])
                ->where('store_id IN (0 , :store_id)')
                ->where('locale = :locale')
                ->order('store_id');
            $bind = [':locale' => $locale, ':store_id' => $storeId];
            $dbData = array_map(function ($value) {
                return htmlspecialchars_decode($value);
            }, $connection->fetchPairs($select, $bind));
            $data = array_replace($data, $dbData);
        }
        return $data;
    }

    /**
     * Retrieve translations array by strings
     *
     * @param array $strings
     * @param int|null $storeId
     *
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
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface|false
     */
    public function getConnection()
    {
        if (!$this->deployedConfig->isDbAvailable()) {
            return false;
        }
        return parent::getConnection();
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

    /**
     * Retrieve store code by store id
     *
     * @param int $storeId
     *
     * @return string
     */
    private function getStoreCode($storeId)
    {
        return $this->scopeResolver->getScope($storeId)->getCode();
    }
}
