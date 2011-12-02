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
 * @package    Zend_Tool
 * @subpackage Framework
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: ZfStandardLibraryDirectory.php 20904 2010-02-04 16:18:18Z matthew $
 */

/**
 * @see Zend_Tool_Project_Context_Filesystem_Directory
 */
#require_once 'Zend/Tool/Project/Context/Filesystem/Directory.php';

/**
 * This class is the front most class for utilizing Zend_Tool_Project
 *
 * A profile is a hierarchical set of resources that keep track of
 * items within a specific project.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Context_Zf_ZfStandardLibraryDirectory extends Zend_Tool_Project_Context_Filesystem_Directory
{

    /**
     * @var string
     */
    protected $_filesystemName = 'Zend';

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'ZfStandardLibraryDirectory';
    }

    /**
     * create()
     *
     */
    public function create()
    {
        parent::create();
        $zfPath = $this->_getZfPath();
        if ($zfPath != false) {
            $zfIterator = new RecursiveDirectoryIterator($zfPath);
            foreach ($rii = new RecursiveIteratorIterator($zfIterator, RecursiveIteratorIterator::SELF_FIRST) as $file) {
                $relativePath = preg_replace('#^'.preg_quote(realpath($zfPath), '#').'#', '', realpath($file->getPath())) . DIRECTORY_SEPARATOR . $file->getFilename();
                if (strpos($relativePath, DIRECTORY_SEPARATOR . '.') !== false) {
                    continue;
                }

                if ($file->isDir()) {
                    mkdir($this->getBaseDirectory() . DIRECTORY_SEPARATOR . $this->getFilesystemName() . $relativePath);
                } else {
                    copy($file->getPathname(), $this->getBaseDirectory() . DIRECTORY_SEPARATOR . $this->getFilesystemName() . $relativePath);
                }

            }
        }
    }

    /**
     * _getZfPath()
     *
     * @return string|false
     */
    protected function _getZfPath()
    {
        #require_once 'Zend/Loader.php';
        foreach (Zend_Loader::explodeIncludePath() as $includePath) {
            if (!file_exists($includePath) || $includePath[0] == '.') {
                continue;
            }

            if (realpath($checkedPath = rtrim($includePath, '\\/') . '/Zend/Loader.php') !== false && file_exists($checkedPath)) {
                return dirname($checkedPath);
            }
        }

        return false;
    }

}
