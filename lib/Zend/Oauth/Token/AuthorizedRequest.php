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
 * @package    Zend_Oauth
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: AuthorizedRequest.php 22662 2010-07-24 17:37:36Z mabe $
 */

/** Zend_Oauth_Token */
#require_once 'Zend/Oauth/Token.php';

/**
 * @category   Zend
 * @package    Zend_Oauth
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Oauth_Token_AuthorizedRequest extends Zend_Oauth_Token
{
    /**
     * @var array
     */
    protected $_data = array();

    /**
     * Constructor
     *
     * @param  null|array $data
     * @param  null|Zend_Oauth_Http_Utility $utility
     * @return void
     */
    public function __construct(array $data = null, Zend_Oauth_Http_Utility $utility = null)
    {
        if ($data !== null) {
            $this->_data = $data;
            $params = $this->_parseData();
            if (count($params) > 0) {
                $this->setParams($params);
            }
        }
        if ($utility !== null) {
            $this->_httpUtility = $utility;
        } else {
            $this->_httpUtility = new Zend_Oauth_Http_Utility;
        }
    }

    /**
     * Retrieve token data
     * 
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Indicate if token is valid
     * 
     * @return bool
     */
    public function isValid()
    {
        if (isset($this->_params[self::TOKEN_PARAM_KEY])
            && !empty($this->_params[self::TOKEN_PARAM_KEY])
        ) {
            return true;
        }
        return false;
    }

    /**
     * Parse string data into array
     * 
     * @return array
     */
    protected function _parseData()
    {
        $params = array();
        if (empty($this->_data)) {
            return;
        }
        foreach ($this->_data as $key => $value) {
            $params[rawurldecode($key)] = rawurldecode($value);
        }
        return $params;
    }
}
