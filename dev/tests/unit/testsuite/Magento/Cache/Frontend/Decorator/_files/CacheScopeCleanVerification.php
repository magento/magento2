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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CacheScopeCleanVerification
{
    /**
     * @var array
     */
    protected $_records = array(
        'elephant' => array('mammal', 'big'),
        'man' => array('mammal', 'medium'),
        'raccoon' => array('mammal', 'small'),
        'ostrich' => array('bird', 'big'),
        'turkey' => array('bird', 'medium'),
        'pigeon' => array('bird', 'small')
    );

    /**
     * Clean records according to tags and mode
     *
     * @param string $mode
     * @param array $tags
     * @return bool
     */
    public function clean($mode, $tags)
    {
        switch ($mode) {
            case Zend_Cache::CLEANING_MODE_MATCHING_TAG:
                $filterFunc = function ($recTags) use ($tags) {
                    return (bool) array_diff($tags, $recTags);
                };
                break;
            case Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG:
                $filterFunc = function ($recTags) use ($tags) {
                    return !array_intersect($recTags, $tags);
                };
                break;
            case Zend_Cache::CLEANING_MODE_ALL:
                $filterFunc = function () {
                    return false;
                };
                break;
            default:
                return false;
        }
        $this->_records = array_filter($this->_records, $filterFunc);
        return true;
    }

    /**
     * Return id of records left
     *
     * @return array
     */
    public function getRecordIds()
    {
        return array_keys($this->_records);
    }
}
