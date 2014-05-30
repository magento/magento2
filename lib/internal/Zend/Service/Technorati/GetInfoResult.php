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
 * @package    Zend_Service
 * @subpackage Technorati
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: GetInfoResult.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * Represents a single Technorati GetInfo query result object.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Technorati
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Technorati_GetInfoResult
{
    /**
     * Technorati author
     *
     * @var     Zend_Service_Technorati_Author
     * @access  protected
     */
    protected $_author;

    /**
     * A list of weblogs claimed by this author
     *
     * @var     array
     * @access  protected
     */
    protected $_weblogs = array();


    /**
     * Constructs a new object object from DOM Document.
     *
     * @param   DomDocument $dom the ReST fragment for this object
     */
    public function __construct(DomDocument $dom)
    {
        $xpath = new DOMXPath($dom);

        /**
         * @see Zend_Service_Technorati_Author
         */
        #require_once 'Zend/Service/Technorati/Author.php';

        $result = $xpath->query('//result');
        if ($result->length == 1) {
            $this->_author = new Zend_Service_Technorati_Author($result->item(0));
        }

        /**
         * @see Zend_Service_Technorati_Weblog
         */
        #require_once 'Zend/Service/Technorati/Weblog.php';

        $result = $xpath->query('//item/weblog');
        if ($result->length >= 1) {
            foreach ($result as $weblog) {
                $this->_weblogs[] = new Zend_Service_Technorati_Weblog($weblog);
            }
        }
    }


    /**
     * Returns the author associated with queried username.
     *
     * @return  Zend_Service_Technorati_Author
     */
    public function getAuthor() {
        return $this->_author;
    }

    /**
     * Returns the collection of weblogs authored by queried username.
     *
     * @return  array of Zend_Service_Technorati_Weblog
     */
    public function getWeblogs() {
        return $this->_weblogs;
    }

}
