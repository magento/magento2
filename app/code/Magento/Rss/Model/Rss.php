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
 * @package     Magento_Rss
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Rss\Model;

/**
 * Auth session model
 *
 * @category   Magento
 * @package    Magento_Rss
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Rss
{
    /**
     * @var array
     */
    protected $_feedArray = array();

    /**
     * @param array $data
     * @return $this
     */
    public function _addHeader($data = array())
    {
        $this->_feedArray = $data;
        return $this;
    }

    /**
     * @param array $entries
     * @return $this
     */
    public function _addEntries($entries)
    {
        $this->_feedArray['entries'] = $entries;
        return $this;
    }

    /**
     * @param array $entry
     * @return $this
     */
    public function _addEntry($entry)
    {
        $this->_feedArray['entries'][] = $entry;
        return $this;
    }

    /**
     * @return array
     */
    public function getFeedArray()
    {
        return $this->_feedArray;
    }

    /**
     * @return string
     */
    public function createRssXml()
    {
        try {
            $rssFeedFromArray = \Zend_Feed::importArray($this->getFeedArray(), 'rss');
            return $rssFeedFromArray->saveXML();
        } catch (\Exception $e) {
            return __('Error in processing xml. %1', $e->getMessage());
        }
    }
}
