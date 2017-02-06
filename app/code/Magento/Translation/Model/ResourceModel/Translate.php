<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Model\ResourceModel;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Config;
use Magento\Translation\App\Config\Type\Translation;

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
     * @var Config
     */
    private $appConfig;

    /**
     * @var DeploymentConfig
     */
    private $deployedConfig;

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
        $locale = (string) $locale;

        $data = $this->getAppConfig()->get(
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
            $dbData = $connection->fetchPairs($select, $bind);
            $data = array_replace($data, $dbData);
        }
        return $data;
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
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface|false
     */
    public function getConnection()
    {
        if (!$this->getDeployedConfig()->isDbAvailable()) {
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
     * @return string
     */
    private function getStoreCode($storeId)
    {
        return $this->scopeResolver->getScope($storeId)->getCode();
    }

    /**
     * @deprecated
     * @return DeploymentConfig
     */
    private function getDeployedConfig()
    {
        if ($this->deployedConfig === null) {
            $this->deployedConfig = ObjectManager::getInstance()->get(DeploymentConfig::class);
        }
        return $this->deployedConfig;
    }

    /**
     * @deprecated
     * @return Config
     */
    private function getAppConfig()
    {
        if ($this->appConfig === null) {
            $this->appConfig = ObjectManager::getInstance()->get(Config::class);
        }
        return $this->appConfig;
    }
}
