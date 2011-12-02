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
 * @subpackage Photos
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: CommentingEnabled.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Extension
 */
#require_once 'Zend/Gdata/Extension.php';

/**
 * @see Zend_Gdata_Photos
 */
#require_once 'Zend/Gdata/Photos.php';

/**
 * Represents the gphoto:commentingEnabled element used by the API.
 * This class represents whether commenting is enabled for a given
 * entry.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Photos
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Photos_Extension_CommentingEnabled extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'gphoto';
    protected $_rootElement = 'commentingEnabled';

    /**
     * Constructs a new Zend_Gdata_Photos_Extension_CommentingEnabled object.
     *
     * @param string $text (optional) Whether commenting should be enabled
     *          or not.
     */
    public function __construct($text = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Photos::$namespaces);
        parent::__construct();
        $this->setText($text);
    }

}
