<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Model\Resource;

/**
 * Visitor log resource
 */
class Visitor extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Primary key auto increment flag
     *
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\String
     */
    protected $string;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\String $string
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\String $string
    ) {
        $this->_date = $date;
        $this->_storeManager = $storeManager;
        $this->string = $string;
        parent::__construct($resource);
    }

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('log_visitor', 'visitor_id');
    }

    /**
     * Prepare data for save
     *
     * @param \Magento\Framework\Model\AbstractModel $visitor
     * @return array
     */
    protected function _prepareDataForSave(\Magento\Framework\Model\AbstractModel $visitor)
    {
        return [
            'visitor_id' => $visitor->getVisitorId(),
            'first_visit_at' => $visitor->getFirstVisitAt(),
            'last_visit_at' => $visitor->getLastVisitAt(),
            'last_url_id' => $visitor->getLastUrlId() ? $visitor->getLastUrlId() : 0,
            'store_id' => $this->_storeManager->getStore()->getId()
        ];
    }

    /**
     * Saving information about url
     *
     * @param   \Magento\Log\Model\Visitor $visitor
     * @return  \Magento\Log\Model\Resource\Visitor
     */
    protected function _saveUrlInfo($visitor)
    {
        $adapter = $this->_getWriteAdapter();
        $data = new \Magento\Framework\Object(
            [
                'url' => $this->string->substr($visitor->getUrl(), 0, 250),
                'referer' => $this->string->substr($visitor->getHttpReferer(), 0, 250),
            ]
        );
        $bind = $this->_prepareDataForTable($data, $this->getTable('log_url_info'));

        $adapter->insert($this->getTable('log_url_info'), $bind);

        $visitor->setLastUrlId($adapter->lastInsertId($this->getTable('log_url_info')));

        return $this;
    }

    /**
     * Save url info before save
     *
     * @param \Magento\Framework\Model\AbstractModel $visitor
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $visitor)
    {
        if (!$visitor->getIsNewVisitor()) {
            $this->_saveUrlInfo($visitor);
        }
        return $this;
    }

    /**
     * Actions after save
     *
     * @param \Magento\Framework\Model\AbstractModel $visitor
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $visitor)
    {
        if ($visitor->getIsNewVisitor()) {
            $this->_saveVisitorInfo($visitor);
            $visitor->setIsNewVisitor(false);
        } else {
            $this->_saveVisitorUrl($visitor);
            if ($visitor->getDoCustomerLogin() || $visitor->getDoCustomerLogout()) {
                $this->_saveCustomerInfo($visitor);
            }
            if ($visitor->getDoQuoteCreate() || $visitor->getDoQuoteDestroy()) {
                $this->_saveQuoteInfo($visitor);
            }
        }
        return $this;
    }

    /**
     * Perform actions after object load
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\Object $object
     * @return \Magento\Framework\Model\Resource\Db\AbstractDb
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterLoad($object);
        // Add information about quote to visitor
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from(
            $this->getTable('log_quote'),
            'quote_id'
        )->where(
            'visitor_id = ?',
            $object->getId()
        )->limit(
            1
        );
        $result = $adapter->query($select)->fetch();
        if (isset($result['quote_id'])) {
            $object->setQuoteId((int)$result['quote_id']);
        }
        return $this;
    }

    /**
     * Saving visitor information
     *
     * @param   \Magento\Log\Model\Visitor $visitor
     * @return  \Magento\Log\Model\Resource\Visitor
     */
    protected function _saveVisitorInfo($visitor)
    {
        $referer = $this->string->cleanString($visitor->getHttpReferer());
        $referer = $this->string->substr($referer, 0, 255);

        $userAgent = $this->string->cleanString($visitor->getHttpUserAgent());
        $userAgent = $this->string->substr($userAgent, 0, 255);

        $charset = $this->string->cleanString($visitor->getHttpAcceptCharset());
        $charset = $this->string->substr($charset, 0, 255);

        $language = $this->string->cleanString($visitor->getHttpAcceptLanguage());
        $language = $this->string->substr($language, 0, 255);

        $data = new \Magento\Framework\Object(
            [
                'visitor_id' => $visitor->getId(),
                'http_referer' => $referer,
                'http_user_agent' => $userAgent,
                'http_accept_charset' => $charset,
                'http_accept_language' => $language,
                'server_addr' => $visitor->getServerAddr(),
                'remote_addr' => $visitor->getRemoteAddr(),
            ]
        );

        $bind = $this->_prepareDataForTable($data, $this->getTable('log_visitor_info'));

        $adapter = $this->_getWriteAdapter();
        $adapter->insert($this->getTable('log_visitor_info'), $bind);

        return $this;
    }

    /**
     * Saving visitor and url relation
     *
     * @param   \Magento\Log\Model\Visitor $visitor
     * @return  \Magento\Log\Model\Resource\Visitor
     */
    protected function _saveVisitorUrl($visitor)
    {
        $data = new \Magento\Framework\Object(
            [
                'url_id' => $visitor->getLastUrlId(),
                'visitor_id' => $visitor->getId(),
                'visit_time' => $this->_date->gmtDate(),
            ]
        );
        $bind = $this->_prepareDataForTable($data, $this->getTable('log_url'));

        $this->_getWriteAdapter()->insert($this->getTable('log_url'), $bind);
        return $this;
    }

    /**
     * Saving information about customer
     *
     * @param   \Magento\Log\Model\Visitor $visitor
     * @return  \Magento\Log\Model\Resource\Visitor
     */
    protected function _saveCustomerInfo($visitor)
    {
        $adapter = $this->_getWriteAdapter();

        if ($visitor->getDoCustomerLogin()) {
            $data = new \Magento\Framework\Object(
                [
                    'visitor_id' => $visitor->getVisitorId(),
                    'customer_id' => $visitor->getCustomerId(),
                    'login_at' => $this->_date->gmtDate(),
                    'store_id' => $this->_storeManager->getStore()->getId(),
                ]
            );
            $bind = $this->_prepareDataForTable($data, $this->getTable('log_customer'));

            $adapter->insert($this->getTable('log_customer'), $bind);
            $visitor->setCustomerLogId($adapter->lastInsertId($this->getTable('log_customer')));
            $visitor->setDoCustomerLogin(false);
        }

        if ($visitor->getDoCustomerLogout() && ($logId = $visitor->getCustomerLogId())) {
            $data = new \Magento\Framework\Object(
                [
                    'logout_at' => $this->_date->gmtDate(),
                    'store_id' => (int)$this->_storeManager->getStore()->getId(),
                ]
            );

            $bind = $this->_prepareDataForTable($data, $this->getTable('log_customer'));

            $condition = ['log_id = ?' => (int)$logId];

            $adapter->update($this->getTable('log_customer'), $bind, $condition);

            $visitor->setDoCustomerLogout(false);
            $visitor->setCustomerId(null);
            $visitor->setCustomerLogId(null);
        }

        return $this;
    }

    /**
     * Saving information about quote
     *
     * @param   \Magento\Log\Model\Visitor $visitor
     * @return  \Magento\Log\Model\Resource\Visitor
     */
    protected function _saveQuoteInfo($visitor)
    {
        $adapter = $this->_getWriteAdapter();
        if ($visitor->getDoQuoteCreate()) {
            $data = new \Magento\Framework\Object(
                [
                    'quote_id' => (int)$visitor->getQuoteId(),
                    'visitor_id' => (int)$visitor->getId(),
                    'created_at' => $this->_date->gmtDate(),
                ]
            );

            $bind = $this->_prepareDataForTable($data, $this->getTable('log_quote'));

            $adapter->insert($this->getTable('log_quote'), $bind);

            $visitor->setDoQuoteCreate(false);
        }

        if ($visitor->getDoQuoteDestroy()) {
            /**
             * We have delete quote from log because if original quote was
             * deleted and Mysql restarted we will get key duplication error
             */
            $condition = ['quote_id = ?' => (int)$visitor->getQuoteId()];

            $adapter->delete($this->getTable('log_quote'), $condition);

            $visitor->setDoQuoteDestroy(false);
            $visitor->setQuoteId(null);
        }
        return $this;
    }
}
