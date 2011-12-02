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
 * @subpackage Gbase
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: SnippetEntry.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Gbase_Entry
 */
#require_once 'Zend/Gdata/Gbase/Entry.php';

/**
 * Concrete class for working with Snippet entries.
 *
 * @link http://code.google.com/apis/base/
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gbase
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Gbase_SnippetEntry extends Zend_Gdata_Gbase_Entry
{
    /**
     * The classname for individual snippet entry elements.
     *
     * @var string
     */
    protected $_entryClassName = 'Zend_Gdata_Gbase_SnippetEntry';
}
