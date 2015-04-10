<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Model;

use Magento\Framework\Data\Collection\Db;
use Magento\Framework\Json\Decoder;
use Magento\Framework\Json\Encoder;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Ui\Model\Resource\Bookmark\Collection;
use Magento\Ui\Model\Resource\Bookmark as ResourceBookmark;

/**
 * Domain class Bookmark
 */
class Bookmark extends AbstractModel
{
    /**
     * @var Encoder
     */
    protected $jsonEncoder;

    /**
     * @var Decoder
     */
    protected $jsonDecoder;

    /**
     * @param Encoder $jsonEncoder
     * @param Decoder $jsonDecoder
     * @param Context $context
     * @param Registry $registry
     * @param ResourceBookmark $resource
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Encoder $jsonEncoder,
        Decoder $jsonDecoder,
        Context $context,
        Registry $registry,
        ResourceBookmark $resource,
        Collection $resourceCollection,
        array $data = []
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const BOOKMARK_ID      = 'bookmark_id';
    const USER_ID          = 'user_id';
    const IDENTIFIER       = 'identifier';
    const TITLE            = 'title';
    const CONFIG           = 'config';
    const CREATED_AT       = 'created_at';
    const UPDATED_AT       = 'updated_at';
    const CURRENT          = 'current';
    /**#@-*/

    /**
     * Get Id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::BOOKMARK_ID);
    }

    /**
     * Get user Id
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->getData(self::USER_ID);
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * Is current
     *
     * @return bool
     */
    public function isCurrent()
    {
        return (bool)$this->getData(self::CURRENT);
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Get config
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->jsonDecoder->decode($this->getData(self::CONFIG));
    }

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set Id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::BOOKMARK_ID, $id);
    }

    /**
     * Set user Id
     *
     * @param int $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        return $this->setData(self::USER_ID, $userId);
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * Set current
     *
     * @param bool $isCurrent
     * @return $this
     */
    public function setCurrent($isCurrent)
    {
        return $this->setData(self::CURRENT, $isCurrent);
    }

    /**
     * Set title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set config
     *
     * @param [] $config
     * @return $this
     */
    public function setConfig($config)
    {
        return $this->setData(self::CONFIG, $this->jsonEncoder->encode($config));
    }

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get current bookmark by identifier for current user
     *
     * @param string $identifier
     * @return $this
     */
    public function getCurrentBookmarkByIdentifier($identifier)
    {
        $collection = $this->_resourceCollection;
        /**
         * @var $collection Collection
         */
        return $collection->filterCurrentForIdentifier($identifier)->load()->getLastItem();
    }

    /**
     * Save bookmark state
     *
     * @param [] $data
     */
    public function saveState($data)
    {
        if (isset($data['namespace'])) {
            $bookmark = $this->getCurrentBookmarkByIdentifier($data['namespace']);

            $sorting = [];
            if (isset($data['sorting']['field'])
                && isset($data['sorting']['direction'])) {
                $sorting['columns'] = [];
                $sorting['columns'][$data['sorting']['field']] = ['sorting' => $data['sorting']['direction']];
            }

            $config = [
                'columns' => isset($data['columns']) ? $data['columns'] : [],
                'filters' => isset($data['filters']) ? $data['filters'] : [],
            ];
            $config = array_replace_recursive($config, $sorting);

            $bookmark->setConfig($config)->save();
        }
    }
}
