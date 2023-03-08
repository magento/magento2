<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model;

use Magento\Framework\App\DesignInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\DesignInterface as ViewDesignInterface;
use Magento\Theme\Model\ResourceModel\Design as ResourceDesign;

/**
 * Design settings change model
 *
 * @method int getStoreId()
 * @method Design setStoreId(int $value)
 * @method string getDesign()
 * @method Design setDesign(string $value)
 * @method string getDateFrom()
 * @method Design setDateFrom(string $value)
 * @method string getDateTo()
 * @method Design setDateTo(string $value)
 */
class Design extends AbstractModel implements IdentityInterface, DesignInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'CORE_DESIGN';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'core_design';

    /**
     * Model cache tag for clear cache in after save and after delete
     *
     * When you use true - all cache will be clean
     *
     * @var string|bool
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param TimezoneInterface $localeDate
     * @param DateTime $dateTime
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Context $context,
        Registry $registry,
        TimezoneInterface $localeDate,
        DateTime $dateTime,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        private ?SerializerInterface $serializer = null
    ) {
        $this->_localeDate = $localeDate;
        $this->_dateTime = $dateTime;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceDesign::class);
    }

    /**
     * Load custom design settings for specified store and date
     *
     * @param string $storeId
     * @param string|null $date
     * @return $this
     */
    public function loadChange($storeId, $date = null)
    {
        if ($date === null) {
            $date = $this->_dateTime->formatDate($this->_localeDate->scopeTimeStamp($storeId), false);
        }

        // md5() here is not for cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
        $changeCacheId = 'design_change_' . md5($storeId . $date);
        $result = $this->_cacheManager->load($changeCacheId);
        if ($result === false) {
            $result = $this->getResource()->loadChange($storeId, $date);
            if (!$result) {
                $result = [];
            }
            $this->_cacheManager->save($this->serializer->serialize($result), $changeCacheId, [self::CACHE_TAG], 86400);
        } else {
            $result = $this->serializer->unserialize($result);
        }

        if ($result) {
            $this->setData($result);
        }

        return $this;
    }

    /**
     * Apply design change from self data into specified design package instance
     *
     * @param ViewDesignInterface $packageInto
     * @return $this
     */
    public function changeDesign(ViewDesignInterface $packageInto)
    {
        $design = $this->getDesign();
        if ($design) {
            $packageInto->setDesignTheme($design);
        }
        return $this;
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
