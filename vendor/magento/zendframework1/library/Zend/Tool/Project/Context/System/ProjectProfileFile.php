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
 * @see Zend_Tool_Project_Context_Filesystem_File
 */
#require_once 'Zend/Tool/Project/Context/Filesystem/File.php';

/**
 * @see Zend_Tool_Project_Context_System_Interface
 */
#require_once 'Zend/Tool/Project/Context/System/Interface.php';

/**
 * @see Zend_Tool_Project_Context_System_NotOverwritable
 */
#require_once 'Zend/Tool/Project/Context/System/NotOverwritable.php';

/**
 * @see Zend_Tool_Project_Profile_FileParser_Xml
 */
#require_once 'Zend/Tool/Project/Profile/FileParser/Xml.php';

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
class Zend_Tool_Project_Context_System_ProjectProfileFile
    extends Zend_Tool_Project_Context_Filesystem_File
    implements Zend_Tool_Project_Context_System_Interface,
               Zend_Tool_Project_Context_System_NotOverwritable
{

    /**
     * @var string
     */
    protected $_filesystemName = '.zfproject.xml';

    /**
     * @var Zend_Tool_Project_Profile
     */
    protected $_profile = null;

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'ProjectProfileFile';
    }

    /**
     * setProfile()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @return Zend_Tool_Project_Context_System_ProjectProfileFile
     */
    public function setProfile($profile)
    {
        $this->_profile = $profile;
        return $this;
    }

    /**
     * save()
     *
     * Proxy to create
     *
     * @return Zend_Tool_Project_Context_System_ProjectProfileFile
     */
    public function save()
    {
        parent::create();
        return $this;
    }

    /**
     * getContents()
     *
     * @return string
     */
    public function getContents()
    {
        $parser = new Zend_Tool_Project_Profile_FileParser_Xml();
        $profile = $this->_resource->getProfile();
        $xml = $parser->serialize($profile);
        return $xml;
    }

}
