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
 * @package    Zend_Search_Lucene
 * @subpackage Index
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * A Zend_Search_Lucene_Index_DocsFilter is used to filter documents while searching.
 *
 * It may or _may_not_ be used for actual filtering, so it's just a hint that upper query limits
 * search result by specified list.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Index
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Index_DocsFilter
{
    /**
     * Set of segment filters:
     *  array( <segmentName> => array(<docId> => <undefined_value>,
     *                                <docId> => <undefined_value>,
     *                                <docId> => <undefined_value>,
     *                                ...                          ),
     *         <segmentName> => array(<docId> => <undefined_value>,
     *                                <docId> => <undefined_value>,
     *                                <docId> => <undefined_value>,
     *                                ...                          ),
     *         <segmentName> => array(<docId> => <undefined_value>,
     *                                <docId> => <undefined_value>,
     *                                <docId> => <undefined_value>,
     *                                ...                          ),
     *         ...
     *       )
     *
     * @var array
     */
    public $segmentFilters = array();
}

