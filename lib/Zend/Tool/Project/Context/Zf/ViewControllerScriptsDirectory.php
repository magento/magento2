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
 * @version    $Id: ViewControllerScriptsDirectory.php 23343 2010-11-15 15:33:22Z ramon $
 */

/**
 * @see Zend_Tool_Project_Context_Filesystem_Directory
 */
#require_once 'Zend/Tool/Project/Context/Filesystem/Directory.php';

/**
 * @see Zend_Filter
 */
#require_once 'Zend/Filter.php';

/**
 * @see Zend_Filter_Word_CamelCaseToDash
 */
#require_once 'Zend/Filter/Word/CamelCaseToDash.php';

/**
 * @see Zend_Filter_StringToLower
 */
#require_once 'Zend/Filter/StringToLower.php';


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
class Zend_Tool_Project_Context_Zf_ViewControllerScriptsDirectory extends Zend_Tool_Project_Context_Filesystem_Directory
{

    /**
     * @var string
     */
    protected $_filesystemName = 'controllerName';

    /**
     * @var name
     */
    protected $_forControllerName = null;

    /**
     * init()
     *
     * @return Zend_Tool_Project_Context_Zf_ViewControllerScriptsDirectory
     */
    public function init()
    {
        $this->_forControllerName = $this->_resource->getAttribute('forControllerName');
        $this->_filesystemName = $this->_convertControllerNameToFilesystemName($this->_forControllerName);
        parent::init();
        return $this;
    }

    /**
     * getPersistentAttributes()
     *
     * @return array
     */
    public function getPersistentAttributes()
    {
        return array(
            'forControllerName' => $this->_forControllerName
            );
    }

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'ViewControllerScriptsDirectory';
    }

    protected function _convertControllerNameToFilesystemName($controllerName)
    {
        $filter = new Zend_Filter();
        $filter->addFilter(new Zend_Filter_Word_CamelCaseToDash())
            ->addFilter(new Zend_Filter_StringToLower());
        return $filter->filter($controllerName);
    }

}
