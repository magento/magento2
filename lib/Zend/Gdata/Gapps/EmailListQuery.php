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
 * @subpackage Gapps
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: EmailListQuery.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Gapps_Query
 */
#require_once('Zend/Gdata/Gapps/Query.php');

/**
 * Assists in constructing queries for Google Apps email list entries.
 * Instances of this class can be provided in many places where a URL is
 * required.
 *
 * For information on submitting queries to a server, see the Google Apps
 * service class, Zend_Gdata_Gapps.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gapps
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Gapps_EmailListQuery extends Zend_Gdata_Gapps_Query
{

    /**
     * A string which, if not null, indicates which email list should
     * be retrieved by this query.
     *
     * @var string
     */
    protected $_emailListName = null;

    /**
     * Create a new instance.
     *
     * @param string $domain (optional) The Google Apps-hosted domain to use
     *          when constructing query URIs.
     * @param string $emailListName (optional) Value for the emailListName
     *          property.
     * @param string $recipient (optional) Value for the recipient
     *          property.
     * @param string $startEmailListName (optional) Value for the
     *          startEmailListName property.
     */
    public function __construct($domain = null, $emailListName = null,
            $recipient = null, $startEmailListName = null)
    {
        parent::__construct($domain);
        $this->setEmailListName($emailListName);
        $this->setRecipient($recipient);
        $this->setStartEmailListName($startEmailListName);
    }

    /**
     * Set the email list name to query for. When set, only lists with a name
     * matching this value will be returned in search results. Set to
     * null to disable filtering by list name.
     *
     * @param string $value The email list name to filter search results by,
     *          or null to disable.
     */
     public function setEmailListName($value)
     {
         $this->_emailListName = $value;
     }

    /**
     * Get the email list name to query for. If no name is set, null will be
     * returned.
     *
     * @see setEmailListName
     * @return string The email list name to filter search results by, or null
     *              if disabled.
     */
    public function getEmailListName()
    {
        return $this->_emailListName;
    }

    /**
     * Set the recipient to query for. When set, only subscribers with an
     * email address matching this value will be returned in search results.
     * Set to null to disable filtering by username.
     *
     * @param string $value The recipient email address to filter search
     *              results by, or null to  disable.
     */
    public function setRecipient($value)
    {
        if ($value !== null) {
            $this->_params['recipient'] = $value;
        }
        else {
            unset($this->_params['recipient']);
        }
    }

    /**
     * Get the recipient email address to query for. If no recipient is set,
     * null will be returned.
     *
     * @see setRecipient
     * @return string The recipient email address to filter search results by,
     *              or null if disabled.
     */
    public function getRecipient()
    {
        if (array_key_exists('recipient', $this->_params)) {
            return $this->_params['recipient'];
        } else {
            return null;
        }
    }

    /**
     * Set the first email list which should be displayed when retrieving
     * a list of email lists.
     *
     * @param string $value The first email list to be returned, or null to
     *              disable.
     */
    public function setStartEmailListName($value)
    {
        if ($value !== null) {
            $this->_params['startEmailListName'] = $value;
        } else {
            unset($this->_params['startEmailListName']);
        }
    }

    /**
     * Get the first email list which should be displayed when retrieving
     * a list of email lists.
     *
     * @return string The first email list to be returned, or null to
     *              disable.
     */
    public function getStartEmailListName()
    {
        if (array_key_exists('startEmailListName', $this->_params)) {
            return $this->_params['startEmailListName'];
        } else {
            return null;
        }
    }

    /**
     * Returns the URL generated for this query, based on it's current
     * parameters.
     *
     * @return string A URL generated based on the state of this query.
     * @throws Zend_Gdata_App_InvalidArgumentException
     */
    public function getQueryUrl()
    {

        $uri = $this->getBaseUrl();
        $uri .= Zend_Gdata_Gapps::APPS_EMAIL_LIST_PATH;
        if ($this->_emailListName !== null) {
            $uri .= '/' . $this->_emailListName;
        }
        $uri .= $this->getQueryString();
        return $uri;
    }

}
