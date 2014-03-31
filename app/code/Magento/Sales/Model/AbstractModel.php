<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model;

/**
 * Sales abstract model
 * Provide date processing functionality
 */
abstract class AbstractModel extends \Magento\Model\AbstractModel
{
    /**
     * @var \Magento\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Stdlib\DateTime $dateTime
     * @param \Magento\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Stdlib\DateTime $dateTime,
        \Magento\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_localeDate = $localeDate;
        $this->dateTime = $dateTime;
    }

    /**
     * Get object store identifier
     *
     * @return int | string | \Magento\Core\Model\Store
     */
    abstract public function getStore();

    /**
     * Processing object after save data
     * Updates relevant grid table records.
     *
     * @return $this
     */
    public function afterCommitCallback()
    {
        if (!$this->getForceUpdateGridRecords()) {
            $this->_getResource()->updateGridRecords($this->getId());
        }
        return parent::afterCommitCallback();
    }

    /**
     * Get object created at date affected current active store timezone
     *
     * @return \Magento\Stdlib\DateTime\Date
     */
    public function getCreatedAtDate()
    {
        return $this->_localeDate->date($this->dateTime->toTimestamp($this->getCreatedAt()), null, null, true);
    }

    /**
     * Get object created at date affected with object store timezone
     *
     * @return \Magento\Stdlib\DateTime\Date
     */
    public function getCreatedAtStoreDate()
    {
        return $this->_localeDate->scopeDate(
            $this->getStore(),
            $this->dateTime->toTimestamp($this->getCreatedAt()),
            true
        );
    }
}
