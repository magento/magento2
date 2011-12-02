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
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Directory.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Search_Lucene_Storage_Directory
{

    /**
     * Closes the store.
     *
     * @return void
     */
    abstract public function close();

    /**
     * Returns an array of strings, one for each file in the directory.
     *
     * @return array
     */
    abstract public function fileList();

    /**
     * Creates a new, empty file in the directory with the given $filename.
     *
     * @param string $filename
     * @return Zend_Search_Lucene_Storage_File
     */
    abstract public function createFile($filename);


    /**
     * Removes an existing $filename in the directory.
     *
     * @param string $filename
     * @return void
     */
    abstract public function deleteFile($filename);

    /**
     * Purge file if it's cached by directory object
     *
     * Method is used to prevent 'too many open files' error
     *
     * @param string $filename
     * @return void
     */
    abstract public function purgeFile($filename);

    /**
     * Returns true if a file with the given $filename exists.
     *
     * @param string $filename
     * @return boolean
     */
    abstract public function fileExists($filename);


    /**
     * Returns the length of a $filename in the directory.
     *
     * @param string $filename
     * @return integer
     */
    abstract public function fileLength($filename);


    /**
     * Returns the UNIX timestamp $filename was last modified.
     *
     * @param string $filename
     * @return integer
     */
    abstract public function fileModified($filename);


    /**
     * Renames an existing file in the directory.
     *
     * @param string $from
     * @param string $to
     * @return void
     */
    abstract public function renameFile($from, $to);


    /**
     * Sets the modified time of $filename to now.
     *
     * @param string $filename
     * @return void
     */
    abstract public function touchFile($filename);


    /**
     * Returns a Zend_Search_Lucene_Storage_File object for a given $filename in the directory.
     *
     * If $shareHandler option is true, then file handler can be shared between File Object
     * requests. It speed-ups performance, but makes problems with file position.
     * Shared handler are good for short atomic requests.
     * Non-shared handlers are useful for stream file reading (especial for compound files).
     *
     * @param string $filename
     * @param boolean $shareHandler
     * @return Zend_Search_Lucene_Storage_File
     */
    abstract public function getFileObject($filename, $shareHandler = true);

}

