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
 * @package     Magento_Reports
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Reports summary collection
 *
 * @category    Magento
 * @package     Magento_Reports
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource\Entity\Summary\Collection;

class AbstractCollection extends \Magento\Data\Collection
{
    /**
     * Entity collection for summaries
     *
     * @var \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected $_entityCollection;

    /**
     * Filters the summaries by some period
     *
     * @param string $periodType
     * @param string|int|null $customStart
     * @param string|int|null $customEnd
     * @return \Magento\Reports\Model\Resource\Entity\Summary\Collection\AbstractCollection
     */
    public function setSelectPeriod($periodType, $customStart = null, $customEnd = null)
    {
        switch ($periodType) {
            case "24h":
                $customStart = \Magento\Date::toTimestamp(true) - 86400;
                $customEnd   = \Magento\Date::toTimestamp(true);
                break;

            case "7d":
                $customStart = \Magento\Date::toTimestamp(true) - 604800;
                $customEnd   = \Magento\Date::toTimestamp(true);
                break;

            case "30d":
                $customStart = \Magento\Date::toTimestamp(true) - 2592000;
                $customEnd   = \Magento\Date::toTimestamp(true);
                break;

            case "1y":
                $customStart = \Magento\Date::toTimestamp(true) - 31536000;
                $customEnd   = \Magento\Date::toTimestamp(true);
                break;

            default:
                if (is_string($customStart)) {
                    $customStart = strtotime($customStart);
                }
                if (is_string($customEnd)) {
                    $customEnd = strtotime($customEnd);
                }
                break;

        }


        return $this;
    }

    /**
     * Set date period
     *
     * @param int $period
     * @return \Magento\Reports\Model\Resource\Entity\Summary\Collection\AbstractCollection
     */
    public function setDatePeriod($period)
    {
        return $this;
    }

    /**
     * Set store filter
     *
     * @param int $storeId
     * @return \Magento\Reports\Model\Resource\Entity\Summary\Collection\AbstractCollection
     */
    public function setStoreFilter($storeId)
    {
        return $this;
    }

    /**
     * Return collection for summaries
     *
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    public function getCollection()
    {
        if (empty($this->_entityCollection)) {
            $this->_initCollection();
        }
        return $this->_entityCollection;
    }

    /**
     * Init collection
     *
     * @return \Magento\Reports\Model\Resource\Entity\Summary\Collection\AbstractCollection
     */
    protected function _initCollection()
    {
        return $this;
    }
}
