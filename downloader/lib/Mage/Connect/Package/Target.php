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
 * @category    Mage
 * @package     Mage_Connect
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class to get targets and their basepath from target.xml.
 *
 * @category    Mage
 * @package     Mage_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Connect_Package_Target
{

    /**
    * Object contains all contents from target.xml.
    *
    * @var array
    */
    protected $_targetMap=null;

    /**
    * Cache for targets.
    *
    * @var array
    */
    protected $_targets;

    /**
    * Retrieve content from target.xml.
    *
    * @return SimpleXMLElement
    */
    protected function _getTargetMap()
    {
        if (is_null($this->_targetMap)) {
            $this->_targetMap = array();
            $this->_targetMap[] = array('name'=>"magelocal" ,'label'=>"Magento Local module file" , 'uri'=>"./app/code/local");
            $this->_targetMap[] = array('name'=>"magecommunity" ,'label'=>"Magento Community module file" , 'uri'=>"./app/code/community");
            $this->_targetMap[] = array('name'=>"magecore" ,'label'=>"Magento Core team module file" , 'uri'=>"./app/code/core");
            $this->_targetMap[] = array('name'=>"magedesign" ,'label'=>"Magento User Interface (layouts, templates)" , 'uri'=>"./app/design");
            $this->_targetMap[] = array('name'=>"mageetc" ,'label'=>"Magento Global Configuration" , 'uri'=>"./app/etc");
            $this->_targetMap[] = array('name'=>"magelib" ,'label'=>"Magento PHP Library file" , 'uri'=>"./lib");
            $this->_targetMap[] = array('name'=>"magelocale" ,'label'=>"Magento Locale language file" , 'uri'=>"./app/locale");
            $this->_targetMap[] = array('name'=>"magemedia" ,'label'=>"Magento Media library" , 'uri'=>"./media");
            $this->_targetMap[] = array('name'=>"mageskin" ,'label'=>"Magento Theme Skin (Images, CSS, JS)" , 'uri'=>"./skin");
            $this->_targetMap[] = array('name'=>"mageweb" ,'label'=>"Magento Other web accessible file" , 'uri'=>".");
            $this->_targetMap[] = array('name'=>"magetest" ,'label'=>"Magento PHPUnit test" , 'uri'=>"./tests");
            $this->_targetMap[] = array('name'=>"mage" ,'label'=>"Magento other" , 'uri'=>".");
        }        
        return $this->_targetMap;
    }

    /**
    * Retrieve targets as associative array from target.xml.
    *
    * @return array
    */
    public function getTargets()
    {
        if (!is_array($this->_targets)) {            
            $this->_targets = array();
            if($this->_getTargetMap()) {           
                foreach ($this->_getTargetMap() as $_target) {
                    $this->_targets[$_target['name']] = (string)$_target['uri'];
                }
            }
        }
        return $this->_targets;
    }

    /**
    * Retrieve tragets with label for select options.
    *
    * @return array
    */
    public function getLabelTargets()
    {
        $targets = array();
        foreach ($this->_getTargetMap() as $_target) {
            $targets[$_target['name']] = $_target['label'];
        }
        return $targets;
    }

    /**
    * Get uri by target's name.
    *
    * @param string $name
    * @return string
    */
    public function getTargetUri($name)
    {
        foreach ($this->getTargets() as $_name=>$_uri) {
            if ($name == $_name) {
                return $_uri;
            }
        }
        return '';
    }


}
