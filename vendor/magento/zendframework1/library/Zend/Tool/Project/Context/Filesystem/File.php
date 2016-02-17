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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Tool_Project_Context_Filesystem_Abstract
 */
#require_once 'Zend/Tool/Project/Context/Filesystem/Abstract.php';

/**
 * This class is the front most class for utilizing Zend_Tool_Project
 *
 * A profile is a hierarchical set of resources that keep track of
 * items within a specific project.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Context_Filesystem_File extends Zend_Tool_Project_Context_Filesystem_Abstract
{

    protected $_fileOnlyContext = null;

    protected $_filesystemName = null;

    protected $_content = null;

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'file';
    }

    /**
     * init()
     *
     * @return Zend_Tool_Project_Context_Filesystem_File
     */
    public function init()
    {
        if ($this->_resource->hasAttribute('filesystemName')) {
            $this->_filesystemName = $this->_resource->getAttribute('filesystemName');
        }

        // check to see if this file is
        if ($this->getName() == 'file') {
            $this->_initFileOnlyContext();
        }

        // @potential-todo check to ensure that this 'file' resource has no children
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
        $returnAttrs = array();
        if ($this->_filesystemName !== null) {
            $returnAttrs['filesystemName'] = $this->_filesystemName;
        }
        return $returnAttrs;
    }

    /**
     * setResource()
     *
     * @param unknown_type $resource
     */
    public function setResource(Zend_Tool_Project_Profile_Resource $resource)
    {
        $this->_resource = $resource;
        $this->_resource->setAppendable(false);
        return $this;
    }

    /**
     * getResource()
     *
     * @return Zend_Tool_Project_Profile_Resource
     */
    public function getResource()
    {
        return $this->_resource;
    }

    /**
     * create()
     *
     * @return Zend_Tool_Project_Context_Filesystem_File
     */
    public function create()
    {
        // check to ensure the parent exists, if not, call it and create it
        if (($parentResource = $this->_resource->getParentResource()) instanceof Zend_Tool_Project_Profile_Resource) {
            if ((($parentContext = $parentResource->getContext()) instanceof Zend_Tool_Project_Context_Filesystem_Abstract)
                && (!$parentContext->exists())) {
                $parentResource->create();
            }
        }


        if (file_exists($this->getPath())) {
            // @todo propt user to determine if its ok to overwrite file
        }

        file_put_contents($this->getPath(), $this->getContents());
        return $this;
    }

    /**
     * delete()
     *
     * @return Zend_Tool_Project_Context_Filesystem_File
     */
    public function delete()
    {
        unlink($this->getPath());
        $this->_resource->setDeleted(true);
        return $this;
    }

    /**
     * getContents()
     *
     * @return null
     */
    public function getContents()
    {
        return $this->_content;
    }

    protected function _initFileOnlyContext()
    {
        if ($this->_resource->hasAttribute('defaultContentCallback')) {
            $contentFunc = $this->_resource->getAttribute('defaultContentCallback');
            if (is_callable($contentFunc)) {
                $this->_content = call_user_func_array($contentFunc, array($this));
            }
        }
        if ($this->_filesystemName == null) {
            $this->_filesystemName = 'file.txt';
        }
    }

}
