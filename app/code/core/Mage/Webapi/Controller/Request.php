<?php
/**
 * Web API request.
 *
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
class Mage_Webapi_Controller_Request extends Zend_Controller_Request_Http
{
    /**#@+
     * Name of query ($_GET) parameters to use in navigation and so on.
     */
    const QUERY_PARAM_REQ_ATTRS = 'attrs';
    const QUERY_PARAM_PAGE_NUM = 'page';
    const QUERY_PARAM_PAGE_SIZE = 'limit';
    const QUERY_PARAM_ORDER_FIELD = 'order';
    const QUERY_PARAM_ORDER_DIR = 'dir';
    const QUERY_PARAM_FILTER = 'filter';
    /**#@-*/

    /** @var string */
    protected $_apiType;

    /**
     * Set current API type.
     *
     * @param string $apiType
     * @param null|string|Zend_Uri $uri
     */
    public function __construct($apiType, $uri = null)
    {
        $this->setApiType($apiType);
        parent::__construct($uri);
    }

    /**
     * Get current API type.
     *
     * @return string
     */
    public function getApiType()
    {
        return $this->_apiType;
    }

    /**
     * Set current API type.
     *
     * @param string $apiType
     */
    public function setApiType($apiType)
    {
        $this->_apiType = $apiType;
    }

    /**
     * Get filter settings passed by API user.
     *
     * @return mixed
     */
    public function getFilter()
    {
        return $this->getQuery(self::QUERY_PARAM_FILTER);
    }

    /**
     * Get sort order direction requested by API user.
     *
     * @return mixed
     */
    public function getOrderDirection()
    {
        return $this->getQuery(self::QUERY_PARAM_ORDER_DIR);
    }

    /**
     * Get sort order field requested by API user.
     *
     * @return mixed
     */
    public function getOrderField()
    {
        return $this->getQuery(self::QUERY_PARAM_ORDER_FIELD);
    }

    /**
     * Retrieve page number requested by API user.
     *
     * @return mixed
     */
    public function getPageNumber()
    {
        return $this->getQuery(self::QUERY_PARAM_PAGE_NUM);
    }

    /**
     * Retrieve page size requested by API user.
     *
     * @return mixed
     */
    public function getPageSize()
    {
        return $this->getQuery(self::QUERY_PARAM_PAGE_SIZE);
    }

    /**
     * Get an array of attribute codes requested by API user.
     *
     * @return array
     */
    public function getRequestedAttributes()
    {
        $include = $this->getQuery(self::QUERY_PARAM_REQ_ATTRS, array());

        //transform comma-separated list
        if (!is_array($include)) {
            $include = explode(',', $include);
        }
        return array_map('trim', $include);
    }
}
