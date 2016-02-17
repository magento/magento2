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
 * @see Zend_Tool_Project_Context_Filesystem_Directory
 */
#require_once 'Zend/Tool/Project/Context/Filesystem/Directory.php';

/**
 * @see Zend_Tool_Project_Context_System_Interface
 */
#require_once 'Zend/Tool/Project/Context/System/Interface.php';

/**
 * @see Zend_Tool_Project_Context_System_TopLevelRestrictable
 */
#require_once 'Zend/Tool/Project/Context/System/TopLevelRestrictable.php';

/**
 * @see Zend_Tool_Project_Context_System_NotOverwritable
 */
#require_once 'Zend/Tool/Project/Context/System/NotOverwritable.php';

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
class Zend_Tool_Project_Context_System_ProjectDirectory
    extends Zend_Tool_Project_Context_Filesystem_Directory
    implements Zend_Tool_Project_Context_System_Interface,
               Zend_Tool_Project_Context_System_NotOverwritable,
               Zend_Tool_Project_Context_System_TopLevelRestrictable
{

    /**
     * @var string
     */
    protected $_filesystemName = null;

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'ProjectDirectory';
    }

    /**
     * init()
     *
     * @return Zend_Tool_Project_Context_System_ProjectDirectory
     */
    public function init()
    {
        // get base path from attributes (would be in path attribute)
        $projectDirectory = $this->_resource->getAttribute('path');

        // if not, get from profile
        if ($projectDirectory == null) {
            $projectDirectory = $this->_resource->getProfile()->getAttribute('projectDirectory');
        }

        // if not, exception.
        if ($projectDirectory == null) {
            #require_once 'Zend/Tool/Project/Exception.php';
            throw new Zend_Tool_Project_Exception('projectDirectory cannot find the directory for this project.');
        }

        $this->_baseDirectory = rtrim($projectDirectory, '\\/');
        return $this;
    }

    /**
     * create()
     *
     * @return Zend_Tool_Project_Context_System_ProjectDirectory
     */
    public function create()
    {
        if (file_exists($this->getPath())) {
            /*
            foreach (new DirectoryIterator($this->getPath()) as $item) {
                if (!$item->isDot()) {
                    if ($registry->getClient()->isInteractive()) {
                        // @todo prompt for override
                    } else {
                        #require_once 'Zend/Tool/Project/Context/Exception.php';
                        throw new Zend_Tool_Project_Context_Exception('This directory is not empty, project creation aborted.');
                    }
                    break;
                }
            }
            */
        }

        parent::create();
        return $this;
    }

}
