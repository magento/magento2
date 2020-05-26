<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Model\ResourceModel;

use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Escaper;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\Store;

/**
 * String translation utilities
 */
class StringUtils extends AbstractDb
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * @var null|string
     */
    protected $scope;

    /**
     * @param Context $context
     * @param ResolverInterface $localeResolver
     * @param ScopeResolverInterface $scopeResolver
     * @param Escaper $escaper
     * @param string $connectionName
     * @param string|null $scope
     */
    public function __construct(
        Context $context,
        ResolverInterface $localeResolver,
        ScopeResolverInterface $scopeResolver,
        Escaper $escaper,
        $connectionName = null,
        $scope = null
    ) {
        $this->_localeResolver = $localeResolver;
        $this->scopeResolver = $scopeResolver;
        $this->escaper = $escaper;
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
     * Load an object
     *
     * @param AbstractModel $object
     * @param String $value
     * @param String $field
     *
     * @return array|$this
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        if (is_string($value)) {
            $select = $this->getConnection()->select()->from(
                $this->getMainTable()
            )->where(
                $this->getMainTable() . '.string=:tr_string'
            );
            $result = $this->getConnection()->fetchRow($select, ['tr_string' => $value]);
            $object->setData($result);
            $this->_afterLoad($object);
            return $result;
        }

        return parent::load($object, $value, $field);
    }

    /**
     * Retrieve select for load
     *
     * @param String $field
     * @param String $value
     * @param AbstractModel $object
     *
     * @return Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->where('store_id = ?', Store::DEFAULT_STORE_ID);
        return $select;
    }

    /**
     * After translation loading
     *
     * @param AbstractModel $object
     *
     * @return $this
     */
    public function _afterLoad(AbstractModel $object)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['store_id', 'translate']
        )->where(
            'string = :translate_string'
        );
        $translations = $connection->fetchPairs($select, ['translate_string' => $object->getString()]);
        $object->setStoreTranslations($translations);
        return parent::_afterLoad($object);
    }

    /**
     * Before save
     *
     * @param AbstractModel $object
     *
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), 'key_id')
            ->where('string = :string')
            ->where('store_id = :store_id');

        $bind = ['string' => $object->getString(), 'store_id' => Store::DEFAULT_STORE_ID];

        $object->setId($connection->fetchOne($select, $bind));
        return parent::_beforeSave($object);
    }

    /**
     * After save
     *
     * @param AbstractModel $object
     *
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['store_id', 'key_id']
        )->where(
            'string = :string'
        );
        $stores = $connection->fetchPairs($select, ['string' => $object->getString()]);

        $translations = $object->getStoreTranslations();

        if (is_array($translations)) {
            foreach ($translations as $storeId => $translate) {
                if ($translate === null || $translate == '') {
                    $where = ['store_id = ?' => $storeId, 'string = ?' => $object->getString()];
                    $connection->delete($this->getMainTable(), $where);
                } else {
                    $data = ['store_id' => $storeId, 'string' => $object->getString(), 'translate' => $translate];

                    if (isset($stores[$storeId])) {
                        $connection->update($this->getMainTable(), $data, ['key_id = ?' => $stores[$storeId]]);
                    } else {
                        $connection->insert($this->getMainTable(), $data);
                    }
                }
            }
        }
        return parent::_afterSave($object);
    }

    /**
     * Delete translates
     *
     * @param string $string
     * @param string $locale
     * @param int|null $storeId
     *
     * @return $this
     */
    public function deleteTranslate($string, $locale = null, $storeId = null)
    {
        if ($locale === null) {
            $locale = $this->_localeResolver->getLocale();
        }

        $where = ['locale = ?' => $locale, 'string = ?' => $string];

        if ($storeId === false) {
            $where['store_id > ?'] = Store::DEFAULT_STORE_ID;
        } elseif ($storeId !== null) {
            $where['store_id = ?'] = $storeId;
        }

        $this->getConnection()->delete($this->getMainTable(), $where);

        return $this;
    }

    /**
     * Save translation
     *
     * @param String $string
     * @param String $translate
     * @param String $locale
     * @param int|null $storeId
     *
     * @return $this
     */
    public function saveTranslate($string, $translate, $locale = null, $storeId = null)
    {
        $string = htmlspecialchars_decode($string);
        $connection = $this->getConnection();
        $table = $this->getMainTable();
        $translate = $this->escaper->escapeHtml($translate);

        if ($locale === null) {
            $locale = $this->_localeResolver->getLocale();
        }

        if ($storeId === null) {
            $storeId = $this->getStoreId();
        }

        $select = $connection->select()->from(
            $table,
            ['key_id', 'translate']
        )->where(
            'store_id = :store_id'
        )->where(
            'locale = :locale'
        )->where(
            'string = :string'
        )->where(
            'crc_string = :crc_string'
        );
        $bind = [
            'store_id' => $storeId,
            'locale' => $locale,
            'string' => $string,
            'crc_string' => crc32($string),
        ];

        if ($row = $connection->fetchRow($select, $bind)) {
            $original = $this->escaper->escapeHtml($string);
            if (strpos($original, '::') !== false) {
                list(, $original) = explode('::', $original);
            }
            if ($original == $translate) {
                $connection->delete($table, ['key_id=?' => $row['key_id']]);
            } elseif ($row['translate'] != $translate) {
                $connection->update($table, ['translate' => $translate], ['key_id=?' => $row['key_id']]);
            }
        } else {
            $connection->insert(
                $table,
                [
                    'store_id' => $storeId,
                    'locale' => $locale,
                    'string' => $string,
                    'translate' => $translate,
                    'crc_string' => crc32($string)
                ]
            );
        }

        return $this;
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
