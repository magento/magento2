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
 * @version    $Id: IncludePathLoader.php 20904 2010-02-04 16:18:18Z matthew $
 */

/**
 * @see Zend_Tool_Framework_Loader_Abstract
 */
#require_once 'Zend/Tool/Framework/Loader/Abstract.php';

/**
 * @see Zend_Tool_Framework_Loader_IncludePathLoader_RecursiveFilterIterator
 */
#require_once 'Zend/Tool/Framework/Loader/IncludePathLoader/RecursiveFilterIterator.php';

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Framework_Loader_IncludePathLoader extends Zend_Tool_Framework_Loader_Abstract
{

    /**
     * _getFiles()
     *
     * @return array Array of files to load
     */
    protected function _getFiles()
    {
        #require_once 'Zend/Loader.php';
        $paths = Zend_Loader::explodeIncludePath();

        // used for checking similarly named files
        $relativeItems   = array();
        $files           = array();
        $isZendTraversed = false;

        foreach ($paths as $path) {

            // default patterns to use
            $filterDenyDirectoryPattern = '.*(/|\\\\).svn';
            $filterAcceptFilePattern    = '.*(?:Manifest|Provider)\.php$';

            if (!file_exists($path) || $path[0] == '.') {
                continue;
            }

            $realIncludePath = realpath($path);

            // ensure that we only traverse a single version of Zend Framework on all include paths
            if (file_exists($realIncludePath . '/Zend/Tool/Framework/Loader/IncludePathLoader.php')) {
                if ($isZendTraversed === false) {
                    $isZendTraversed = true;
                } else {
                    // use the deny directory pattern that includes the path to 'Zend', it will not be accepted
                    $filterDenyDirectoryPattern = '.*((/|\\\\).svn|' . preg_quote($realIncludePath . DIRECTORY_SEPARATOR) . 'Zend)';
                }
            }

            // create recursive directory iterator
            $rdi = new RecursiveDirectoryIterator($path);

            // pass in the RecursiveDirectoryIterator & the patterns
            $filter = new Zend_Tool_Framework_Loader_IncludePathLoader_RecursiveFilterIterator(
                $rdi,
                $filterDenyDirectoryPattern,
                $filterAcceptFilePattern
                );

            // build the rii with the filter
            $iterator = new RecursiveIteratorIterator($filter);

            // iterate over the accepted items
            foreach ($iterator as $item) {
                $file = (string)$item;
                if($this->_fileIsBlacklisted($file)) {
                    continue;
                }

                // ensure that the same named file from separate include_paths is not loaded
                $relativeItem = preg_replace('#^' . preg_quote($realIncludePath . DIRECTORY_SEPARATOR, '#') . '#', '', $item->getRealPath());

                // no links allowed here for now
                if ($item->isLink()) {
                    continue;
                }

                // no items that are relavitely the same are allowed
                if (in_array($relativeItem, $relativeItems)) {
                    continue;
                }

                $relativeItems[] = $relativeItem;
                $files[] = $item->getRealPath();
            }
        }

        return $files;
    }

    /**
     *
     * @param  string $file
     * @return bool
     */
    protected function _fileIsBlacklisted($file)
    {
        $blacklist = array(
            "PHPUnit".DIRECTORY_SEPARATOR."Framework",
            "Zend".DIRECTORY_SEPARATOR."OpenId".DIRECTORY_SEPARATOR."Provider"
        );

        foreach($blacklist AS $blacklitedPattern) {
            if(strpos($file, $blacklitedPattern) !== false) {
                return true;
            }
        }
        return false;
    }
}
