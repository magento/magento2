<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Health
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Query.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Query
 */
#require_once('Zend/Gdata/Query.php');

/**
 * Assists in constructing queries for Google Health
 *
 * @link http://code.google.com/apis/health
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Health
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Health_Query extends Zend_Gdata_Query
{
    /**
     * URI of a user's profile feed.
     */
    const HEALTH_PROFILE_FEED_URI =
        'https://www.google.com/health/feeds/profile/default';

    /**
     * URI of register (notices) feed.
     */
    const HEALTH_REGISTER_FEED_URI =
        'https://www.google.com/health/feeds/register/default';

    /**
     * Namespace for an item category
     */
    const ITEM_CATEGORY_NS = 'http://schemas.google.com/health/item';

    /**
     * The default URI for POST methods
     *
     * @var string
     */
    protected $_defaultFeedUri = self::HEALTH_PROFILE_FEED_URI;

    /**
     * Sets the digest parameter's value.
     *
     * @param string $value
     * @return Zend_Gdata_Health_Query Provides a fluent interface
     */
    public function setDigest($value)
    {
        if ($value !== null) {
            $this->_params['digest'] = $value;
        }
        return $this;
    }

    /**
     * Returns the digest parameter's value.
     *
     * @return string The value set for the digest parameter.
     */
    public function getDigest()
    {
        if (array_key_exists('digest', $this->_params)) {
            return $this->_params['digest'];
        } else {
            return null;
        }
    }

    /**
     * Setter for category queries.
     *
     * @param string $item A category to query.
     * @param string $name (optional) A specific item to search a category for.
     *     An example would be 'Lipitor' if $item is set to 'medication'.
     * @return Zend_Gdata_Health_Query Provides a fluent interface
     */
    public function setCategory($item, $name = null)
    {
        $this->_category = $item .
            ($name ? '/' . urlencode('{' . self::ITEM_CATEGORY_NS . '}' . $name) : null);
        return $this;
    }

    /**
     * Returns the query object's category.
     *
     * @return string id
     */
    public function getCategory()
    {
        return $this->_category;
    }

    /**
     * Setter for the grouped parameter.
     *
     * @param string $value setting a count of results per group.
     * @return Zend_Gdata_Health_Query Provides a fluent interface
     */
    public function setGrouped($value)
    {
        if ($value !== null) {
            $this->_params['grouped'] = $value;
        }
        return $this;
    }

    /**
     * Returns the value set for the grouped parameter.
     *
     * @return string grouped parameter.
     */
    public function getGrouped()
    {
        if (array_key_exists('grouped', $this->_params)) {
            return $this->_params['grouped'];
        } else {
            return null;
        }
    }

    /**
     * Setter for the max-results-group parameter.
     *
     * @param int $value Specifies the maximum number of groups to be
     *     retrieved. Must be an integer value greater than zero. This parameter
     *     is only valid if grouped=true.
     * @return Zend_Gdata_Health_Query Provides a fluent interface
     */
    public function setMaxResultsGroup($value)
    {
        if ($value !== null) {
            if ($value <= 0 || $this->getGrouped() !== 'true') {
                #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                throw new Zend_Gdata_App_InvalidArgumentException(
                    'The max-results-group parameter must be set to a value
                    greater than 0 and can only be used if grouped=true');
            } else {
              $this->_params['max-results-group'] = $value;
            }
        }
        return $this;
    }

    /**
     *  Returns the value set for max-results-group.
     *
     * @return int Returns max-results-group parameter.
     */
    public function getMaxResultsGroup()
    {
        if (array_key_exists('max-results-group', $this->_params)) {
            return $this->_params['max-results-group'];
        } else {
            return null;
        }
    }

    /**
     *  Setter for the max-results-group parameter.
     *
     * @param int $value Specifies the maximum number of records to be
     *     retrieved from each group.  The limits that you specify with this
     *     parameter apply to all groups. Must be an integer value greater than
     *     zero. This parameter is only valid if grouped=true.
     * @return Zend_Gdata_Health_Query Provides a fluent interface
     */
    public function setMaxResultsInGroup($value)
    {
        if ($value !== null) {
            if ($value <= 0 || $this->getGrouped() !== 'true') {
              throw new Zend_Gdata_App_InvalidArgumentException(
                  'The max-results-in-group parameter must be set to a value
                  greater than 0 and can only be used if grouped=true');
            } else {
              $this->_params['max-results-in-group'] = $value;
            }
        }
        return $this;
    }

    /**
     *  Returns the value set for max-results-in-group.
     *
     * @return int Returns max-results-in-group parameter.
     */
    public function getMaxResultsInGroup()
    {
        if (array_key_exists('max-results-in-group', $this->_params)) {
            return $this->_params['max-results-in-group'];
        } else {
            return null;
        }
    }

    /**
     * Setter for the start-index-group parameter.
     *
     * @param int $value Retrieves only items whose group ranking is at
     *     least start-index-group. This should be set to a 1-based index of the
     *     first group to be retrieved. The range is applied per category.
     *     This parameter is only valid if grouped=true.
     * @return Zend_Gdata_Health_Query Provides a fluent interface
     */
    public function setStartIndexGroup($value)
    {
        if ($value !== null && $this->getGrouped() !== 'true') {
            throw new Zend_Gdata_App_InvalidArgumentException(
                'The start-index-group can only be used if grouped=true');
        } else {
          $this->_params['start-index-group'] = $value;
        }
        return $this;
    }

    /**
     *  Returns the value set for start-index-group.
     *
     * @return int Returns start-index-group parameter.
     */
    public function getStartIndexGroup()
    {
        if (array_key_exists('start-index-group', $this->_params)) {
            return $this->_params['start-index-group'];
        } else {
            return null;
        }
    }

    /**
     *  Setter for the start-index-in-group parameter.
     *
     * @param int $value  A 1-based index of the records to be retrieved from
     *     each group. This parameter is only valid if grouped=true.
     * @return Zend_Gdata_Health_Query Provides a fluent interface
     */
    public function setStartIndexInGroup($value)
    {
        if ($value !== null && $this->getGrouped() !== 'true') {
            throw new Zend_Gdata_App_InvalidArgumentException('start-index-in-group');
        } else {
          $this->_params['start-index-in-group'] = $value;
        }
        return $this;
    }

    /**
     * Returns the value set for start-index-in-group.
     *
     * @return int Returns start-index-in-group parameter.
     */
    public function getStartIndexInGroup()
    {
        if (array_key_exists('start-index-in-group', $this->_params)) {
            return $this->_params['start-index-in-group'];
        } else {
            return null;
        }
    }
}
