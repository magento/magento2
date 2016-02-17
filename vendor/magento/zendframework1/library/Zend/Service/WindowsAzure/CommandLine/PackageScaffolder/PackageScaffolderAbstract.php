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
 * @package    Zend_Service_WindowsAzure
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure_CommandLine
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */ 
abstract class Zend_Service_WindowsAzure_CommandLine_PackageScaffolder_PackageScaffolderAbstract
{
	/**
	 * Invokes the scaffolder.
	 *
	 * @param Phar $phar Phar archive containing the current scaffolder.
	 * @param string $root Path Root path.
	 * @param array $options Options array (key/value).
	 */
	abstract public function invoke(Phar $phar, $rootPath, $options = array());
	
	/**
	 * Writes output to STDERR, followed by a newline (optional)
	 * 
	 * @param string $message
	 * @param string $newLine
	 */
	protected function log($message, $newLine = true)
	{
		if (error_reporting() === 0) {
			return;
		}
		file_put_contents('php://stderr', $message . ($newLine ? "\r\n" : ''));
	}
	
	/**
	 * Extract resources to a file system path
	 * 
	 * @param Phar $phar Phar archive.
	 * @param string $path Output path root.
	 */
	protected function extractResources(Phar $phar, $path)
	{
		$this->deleteDirectory($path);
		$phar->extractTo($path);
		@unlink($path . '/index.php');
		@unlink($path . '/build.bat');
		$this->copyDirectory($path . '/resources', $path, false);
		$this->deleteDirectory($path . '/resources');
	}
	
	/**
	 * Apply file transforms.
	 * 
	 * @param string $rootPath Root path.
	 * @param array $values Key/value array.
	 */
	protected function applyTransforms($rootPath, $values)
	{
        if (is_null($rootPath) || !is_string($rootPath) || empty($rootPath)) {
            throw new InvalidArgumentException("Undefined \"rootPath\"");
        }
                        
        if (is_dir($rootPath)) {
            $d = dir($rootPath);
            while ( false !== ( $entry = $d->read() ) ) {
                if ( $entry == '.' || $entry == '..' ) {
                    continue;
                }
                $entry = $rootPath . '/' . $entry; 
                
                $this->applyTransforms($entry, $values);
            }
            $d->close();
        } else {
        	$contents = file_get_contents($rootPath);
        	foreach ($values as $key => $value) {
        		$contents = str_replace('$' . $key . '$', $value, $contents);
        	}
            file_put_contents($rootPath, $contents);
        }
        
        return true;
	}
	
	/**
     * Create directory
     * 
     * @param string  $path           Path of directory to create.
     * @param boolean $abortIfExists  Abort if directory exists.
     * @param boolean $recursive      Create parent directories if not exist.
     * 
     * @return boolean
     */
    protected function createDirectory($path, $abortIfExists = true, $recursive = true) {
        if (is_null($path) || !is_string($path) || empty($path)) {
            throw new InvalidArgumentException ("Undefined \"path\"" );        
        }
                
        if (is_dir($path) && $abortIfExists) {
            return false;       
        }
        
        if (is_dir($path) ) {
            @chmod($path, '0775');
            if (!self::deleteDirectory($path) ) {
                throw new RuntimeException("Failed to delete \"{$path}\".");
            }
        }
            
        if (!mkdir($path, '0775', $recursive) || !is_dir($path)) {
            throw new RuntimeException( "Failed to create directory \"{$path}\"." );
        }

        return true;
    }
    
    /**
     * Fully copy a source directory to a target directory.
     * 
     * @param string  $sourcePath   Source directory
     * @param string  $destinationPath   Target directory
     * @param boolean $abortIfExists Query re-creating target directory if exists
     * @param octal   $mode           Changes access mode
     * 
     * @return boolean
     */
    protected function copyDirectory($sourcePath, $destinationPath, $abortIfExists = true, $mode = '0775') {
        $mode = $mode & ~0002;

        if (is_null($sourcePath) || !is_string($sourcePath) || empty($sourcePath)) {
            throw new InvalidArgumentException("Undefined \"sourcePath\"");
        }
        
        if (is_null($destinationPath) || !is_string($destinationPath) || empty($destinationPath)) {
        	throw new InvalidArgumentException("Undefined \"destinationPath\"");
        }
                    
        if (is_dir($destinationPath) && $abortIfExists) {
            return false;
        }
                        
        if (is_dir($sourcePath)) {
            if (!is_dir($destinationPath) && !mkdir($destinationPath, $mode)) {
                throw new RuntimeException("Failed to create target directory \"{$destinationPath}\"" );
            }
            $d = dir($sourcePath);
            while ( false !== ( $entry = $d->read() ) ) {
                if ( $entry == '.' || $entry == '..' ) {
                    continue;
                }
                $strSourceEntry = $sourcePath . '/' . $entry; 
                $strTargetEntry = $destinationPath . '/' . $entry;
                if (is_dir($strSourceEntry) ) {
                    $this->copyDirectory(
                    	$strSourceEntry, 
                    	$strTargetEntry, 
                    	false, 
                    	$mode
                    );
                    continue;
                }
                if (!copy($strSourceEntry, $strTargetEntry) ) {
                    throw new RuntimeException (
                        "Failed to copy"
                        . " file \"{$strSourceEntry}\""
                        . " to \"{$strTargetEntry}\"" 
                    );
                }
            }
            $d->close();
        } else {
            if (!copy($sourcePath, $destinationPath)) {
                throw new RuntimeException (
                    "Failed to copy"
                    . " file \"{$sourcePath}\""
                    . " to \"{$destinationPath}\"" 
                    
                );
            }
        }
        
        return true;
    }
    
    /**
     * Delete directory and all of its contents;
     * 
     * @param string $path Directory path
     * @return boolean
     */
    protected function deleteDirectory($path) 
    {
        if (is_null($path) || !is_string($path) || empty($path)) {
            throw new InvalidArgumentException( "Undefined \"path\"" );        
        }
        
        $handleDir = false;
        if (is_dir($path) ) {    
            $handleDir = @opendir($path);
        }
        if (!$handleDir) {
            return false;
        }
        @chmod($path, 0775);
        while ($file = readdir($handleDir)) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            
            $fsEntity = $path . "/" . $file;
            
            if (is_dir($fsEntity)) {
                $this->deleteDirectory($fsEntity);
                continue;
            }
            
            if (is_file($fsEntity)) {
                @unlink($fsEntity);
                continue;
            }
            
            throw new LogicException (
                "Unexpected file type: \"{$fsEntity}\"" 
            );
        }
        
        @chmod($path, 0775);        
        closedir($handleDir);
        @rmdir($path);
                     
        return true;
    }
}
