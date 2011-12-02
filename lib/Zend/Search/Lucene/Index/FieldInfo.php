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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: FieldInfo.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Index
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Index_FieldInfo
{
    public $name;
    public $isIndexed;
    public $number;
    public $storeTermVector;
    public $normsOmitted;
    public $payloadsStored;

    public function __construct($name, $isIndexed, $number, $storeTermVector, $normsOmitted = false, $payloadsStored = false)
    {
        $this->name            = $name;
        $this->isIndexed       = $isIndexed;
        $this->number          = $number;
        $this->storeTermVector = $storeTermVector;
        $this->normsOmitted    = $normsOmitted;
        $this->payloadsStored  = $payloadsStored;
    }
}

