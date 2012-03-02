<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Varien
 * @package    Varien_Directory
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Varien Directory Collection
 * *
 * @category   Varien
 * @package    Varien_Directory
 * @author      Magento Core Team <core@magentocommerce.com>
 */

require_once('Varien/Data/Collection.php');
require_once('Varien/Directory/Factory.php');
require_once('Varien/Directory/IFactory.php');

class Varien_Directory_Collection extends Varien_Data_Collection implements IFactory{
    protected $_path='';
    protected $_dirName='';
    protected $_recursionLevel=0;
    protected $_isRecursion;
    protected $_filters = array();
    /**
     * Constructor
     *
     * @param   string $path - path to directory
     * @param   bool $is_recursion - use or not recursion
     * @return  none
     */
    public function __construct($path,$isRecursion=true,$recursionLevel = 0)
    {
        parent::__construct();
        $this->setPath($path);
        $this->_dirName = $this->lastDir();
        $this->setRecursion($isRecursion);
        $this->setRecursionLevel($recursionLevel);
        if($this->getRecursion() || $this->getRecursionLevel()==0){
            $this->parseDir();
        }
    }
    /**
     * Get name of this directory
     *
     * @return  string - name of this directory
     */
    public function getDirName()
    {
        return $this->_dirName;
    }
    /**
     * Get recursion
     *
     * @return  bool - is or not recursion
     */
    public function getRecursion()
    {
        return $this->_isRecursion;
    }
    /**
     * Get recursion level
     *
     * @return  int - recursion level
     */
    public function getRecursionLevel()
    {
        return $this->_recursionLevel;
    }
    /**
     * Get path
     *
     * @return  string - path to this directory
     */
    public function getPath()
    {
        return $this->_path;
    }
    /**
     * Set path to this directory
     * @param   string $path - path to this directory
     * @param   bool $isRecursion - use or not recursion
     * @return  none
     */
    public function setPath($path, $isRecursion='')
    {
        if(is_dir($path)){
            if(isset($this->_path) && $this->_path!=$path && $this->_path!=''){
                $this->_path = $path;
                if($isRecursion!='')$this->_isRecursion = $isRecursion;
                $this->parseDir();
            } else {
                $this->_path = $path;
            }
        } else {
            throw new Exception($path. 'is not dir.');
        }
    }
    /**
     * Set recursion
     *
     * @param   bool $isRecursion - use or not recursion
     * @return  none
     */
    public function setRecursion($isRecursion)
    {
        $this->_isRecursion = $isRecursion;
    }
    /**
     * Set level of recursion
     *
     * @param   int $recursionLevel - level of recursion
     * @return  none
     */
    public function setRecursionLevel($recursionLevel)
    {
        $this->_recursionLevel = $recursionLevel;
    }
    /**
     * get latest dir in the path
     *
     * @param   string $path - path to directory
     * @return  string - latest dir in the path
     */
    public function lastDir()
    {
        return self::getLastDir($this->getPath());
    }
    /**
     * get latest dir in the path
     *
     * @param   string $path - path to directory
     * @return  string - latest dir in the path
     */
    static public function getLastDir($path){
        if($path=='') $path = $this->getPath();
        $last = strrpos($path, "/");
        return substr($path,$last+1);
    }
    /**
     * add item to collection
     *
     * @param   IFactory $item - item of collection
     * @return  none
     */
    public function addItem(IFactory $item)
    {
        $this->_items[] = $item;
    }
    /**
     * parse this directory
     *
     * @return  none
     */
    protected function parseDir()
    {
        $this->clear();
        $iter = new RecursiveDirectoryIterator($this->getPath());
        while ($iter->valid()) {
            $curr = (string)$iter->getSubPathname();
            if (!$iter->isDot() && $curr[0]!='.'){
                $this->addItem(Varien_Directory_Factory::getFactory($iter->current(),$this->getRecursion(),$this->getRecursionLevel()));
            }
            $iter->next();
        }
    }
    /**
     * set filter using
     *
     * @param   bool $useFilter - filter using
     * @return  none
     */
    public function useFilter($useFilter)
    {
        $this->_renderFilters();
        $this->walk('useFilter', array($useFilter));
    }
    /**
     * get files names of current collection
     *
     * @return  array - files names of current collection
     */
    public function filesName()
    {
        $files = array();
        $this->getFilesName($files);
        return $files;

    }
    /**
     * get files names of current collection
     *
     * @param   array $files - array of files names
     * @return  none
     */
    public function getFilesName(&$files)
    {
        $this->walk('getFilesName', array(&$files));
    }
    /**
     * get files paths of current collection
     *
     * @return  array - files paths of current collection
     */
    public function filesPaths()
    {
        $paths = array();
        $this->getFilesPaths($paths);
        return $paths;
    }
    /**
     * get files paths of current collection
     *
     * @param   array $files - array of files paths
     * @return  none
     */
    public function getFilesPaths(&$paths)
    {
        $this->walk('getFilesPaths', array(&$paths));
    }
    /**
     * get SplFileObject objects of files of current collection
     *
     * @return  array - array of SplFileObject objects
     */
    public function filesObj()
    {
        $objs = array();
        $this->getFilesObj($objs);
        return $objs;
    }
    /**
     * get SplFileObject objects of files of current collection
     *
     * @param   array $objs - array of SplFileObject objects
     * @return  none
     */
    public function getFilesObj(&$objs)
    {
        $this->walk('getFilesObj', array(&$objs));
    }
    /**
     * get names of dirs of current collection
     *
     * @return  array - array of names of dirs
     */
    public function dirsName()
    {
        $dir = array();
        $this->getDirsName($dir);
        return $dir;
    }
    /**
     * get names of dirs of current collection
     *
     * @param   array $dirs - array of names of dirs
     * @return  none
     */
    public function getDirsName(&$dirs)
    {
        $this->walk('getDirsName', array(&$dirs));
        if($this->getRecursionLevel()>0)
        $dirs[] = $this->getDirName();
    }
    /**
     * set filters for files
     *
     * @param   array $filter - array of filters
     * @return  none
     */
    protected function setFilesFilter($filter)
    {
        $this->walk('setFilesFilter', array($filter));
    }
    /**
     * display this collection as array
     *
     * @return  array
     */
    public function __toArray()
    {
        $arr = array();
        $this->toArray($arr);
        return $arr;
    }
    /**
     * display this collection as array
     * @param   array &$arr - this collection array
     * @return  none
     */
    public function toArray(&$arr)
    {
        if($this->getRecursionLevel()>0){
            $arr[$this->getDirName()] = array();
            $this->walk('toArray', array(&$arr[$this->getDirName()]));
        } else {
            $this->walk('toArray', array(&$arr));
        }
    }
    /**
     * get this collection as xml
     * @param   bool $addOpenTag - add or not header of xml
     * @param   string $rootName - root element name
     * @return  none
     */
    public function __toXml($addOpenTag=true,$rootName='Struct')
    {
        $xml='';
        $this->toXml($xml,$addOpenTag,$rootName);
        return $xml;
    }
    /**
     * get this collection as xml
     * @param   string &$xml - xml
     * @param   bool $addOpenTag - add or not header of xml
     * @param   string $rootName - root element name
     * @return  none
     */
    public function toXml(&$xml,$recursionLevel=0,$addOpenTag=true,$rootName='Struct')
    {
        if($recursionLevel==0 ){
            $xml = '';
            if($addOpenTag)
            $xml.= '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $xml.= '<'.$rootName.'>'."\n";
        }
        $recursionLevel = $this->getRecursionLevel();
        $xml.= str_repeat("\t",$recursionLevel+1)."<$this->_dirName>\n";
        $this->walk('toXml', array(&$xml,$recursionLevel,$addOpenTag,$rootName));
        $xml.= str_repeat("\t",$recursionLevel+1)."</$this->_dirName>"."\n";
        if($recursionLevel==0 ){
            $xml.= '</'.$rootName.'>'."\n";
        }
    }
    /**
     * apply filters
     * @return  none
     */
    protected function _renderFilters()
    {
        $exts = array();
        $names = array();
        $regName = array();
        foreach ($this->_filters as $filter){
            switch ($filter['field']){
                case 'extension':
                    if(is_array($filter['value'])){
                        foreach ($filter['value'] as $value){
                            $exts[] = $value;
                        }
                    } else {
                        $exts[] = $filter['value'];
                    }
                    break;
                case 'name':
                    if(is_array($filter['value'])){
                        foreach ($filter['value'] as $value){
                            $names[] = $filter['value'];
                        }
                    } else {
                        $names[] = $filter['value'];
                    }
                    break;
                case 'regName':
                    if(is_array($filter['value'])){
                        foreach ($filter['value'] as $value){
                            $regName[] = $filter['value'];
                        }
                    } else {
                        $regName[] = $filter['value'];
                    }
                    break;
            }
        }
        $filter = array();
        if(count($exts)>0) {
            $filter['extension'] = $exts;
        } else {
            $filter['extension'] = null;
        }
        if(count($names)>0) {
            $filter['name']=$names;
        } else {
            $filter['name']=null;
        }
        if(count($regName)>0) {

            $filter['regName']=$regName;
        } else {
            $filter['regName']=null;
        }
        $this->setFilesFilter($filter);
    }
    /**
     * add filter
     * @return  none
     */
    public function addFilter($field, $value)
    {
        $filter = array();
        $filter['field']   = $field;
        $filter['value']   = $value;
        $this->_filters[] = $filter;
        $this->_isFiltersRendered = false;
        $this->walk('addFilter',array($field, $value));
        return $this;
    }
}


/* Example */
/*
 $a = new Varien_Directory_Collection('/usr/home/vasily/dev/magento/lib',false);

 $a->addFilter("extension","php");

 $a->useFilter(true);

 print "-----------------------\n";
 print_r($a->filesName());

 $a->setPath('/usr/home/vasily/dev/magento/lib/Varien/Image',true);
 $a->useFilter(true);

 print "-----------------------\n";
 print_r($a->filesName());

 print "-----------------------\n";
 $filesObj = $a->filesObj();
 print $filesObj[0]->fgets();
 print $filesObj[0]->fgets();
 print $filesObj[0]->fgets();
 print $filesObj[0]->fgets();
 print $filesObj[0]->fgets();
 print $filesObj[0]->fgets();

 */



?>