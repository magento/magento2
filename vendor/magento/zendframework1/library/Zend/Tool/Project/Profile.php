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
 * @see Zend_Tool_Project_Profile_FileParser_Xml
 */
#require_once 'Zend/Tool/Project/Profile/FileParser/Xml.php';

/**
 * @see Zend_Tool_Project_Profile_Resource_Container
 */
#require_once 'Zend/Tool/Project/Profile/Resource/Container.php';

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
class Zend_Tool_Project_Profile extends Zend_Tool_Project_Profile_Resource_Container
{

    /**
     * @var bool
     */
    protected static $_traverseEnabled = false;

    /**
     * Constructor, standard usage would allow the setting of options
     *
     * @param array $options
     * @return bool
     */
    public function __construct($options = null)
    {
        if ($options) {
            $this->setOptions($options);
        }

        $this->_topResources = new Zend_Tool_Project_Profile_Resource_Container();
    }

    /**
     * Process options and either set a profile property or
     * set a profile 'attribute'
     *
     * @param array $options
     */
    public function setOptions(Array $options)
    {
        $this->setAttributes($options);
    }

    /**
     * getIterator() - reqruied by the RecursiveIterator interface
     *
     * @return RecursiveIteratorIterator
     */
    public function getIterator()
    {
        #require_once 'Zend/Tool/Project/Profile/Iterator/EnabledResourceFilter.php';

        return new RecursiveIteratorIterator(
            new Zend_Tool_Project_Profile_Iterator_EnabledResourceFilter($this),
            RecursiveIteratorIterator::SELF_FIRST
            );
    }

    /**
     * loadFromData() - Load a profile from data provided by the
     * 'profilData' attribute
     *
     */
    public function loadFromData()
    {
        if (!isset($this->_attributes['profileData'])) {
            #require_once 'Zend/Tool/Project/Exception.php';
            throw new Zend_Tool_Project_Exception('loadFromData() must have "profileData" set.');
        }

        $profileFileParser = new Zend_Tool_Project_Profile_FileParser_Xml();
        $profileFileParser->unserialize($this->_attributes['profileData'], $this);

        $this->rewind();
    }

    /**
     * isLoadableFromFile() - can a profile be loaded from a file
     *
     * wether or not a profile can be loaded from the
     * file in attribute 'projectProfileFile', or from a file named
     * '.zfproject.xml' inside a directory in key 'projectDirectory'
     *
     * @return bool
     */
    public function isLoadableFromFile()
    {
        if (!isset($this->_attributes['projectProfileFile']) && !isset($this->_attributes['projectDirectory'])) {
            return false;
        }

        if (isset($this->_attributes['projectProfileFile'])) {
            $projectProfileFilePath = $this->_attributes['projectProfileFile'];
            if (!file_exists($projectProfileFilePath)) {
                return false;
            }
        } else {
            $projectProfileFilePath = rtrim($this->_attributes['projectDirectory'], '/\\') . '/.zfproject.xml';
            if (!file_exists($projectProfileFilePath)) {
                return false;
            }
        }

        return true;
    }

    /**
     * loadFromFile() - Load data from file
     *
     * this attempts to load a project profile file from a variety of locations depending
     * on what information the user provided vie $options or attributes, specifically the
     * 'projectDirectory' or 'projectProfileFile'
     *
     */
    public function loadFromFile()
    {
        // if no data is supplied, need either a projectProfileFile or a projectDirectory
        if (!isset($this->_attributes['projectProfileFile']) && !isset($this->_attributes['projectDirectory'])) {
            #require_once 'Zend/Tool/Project/Exception.php';
            throw new Zend_Tool_Project_Exception('loadFromFile() must have at least "projectProfileFile" or "projectDirectory" set.');
        }

        if (isset($this->_attributes['projectProfileFile'])) {
            $projectProfileFilePath = $this->_attributes['projectProfileFile'];
            if (!file_exists($projectProfileFilePath)) {
                #require_once 'Zend/Tool/Project/Exception.php';
                throw new Zend_Tool_Project_Exception('"projectProfileFile" was supplied but file was not found at location ' . $projectProfileFilePath);
            }
            $this->_attributes['projectDirectory'] = dirname($projectProfileFilePath);
        } else {
            $projectProfileFilePath = rtrim($this->_attributes['projectDirectory'], '/\\') . '/.zfproject.xml';
            if (!file_exists($projectProfileFilePath)) {
                #require_once 'Zend/Tool/Project/Exception.php';
                throw new Zend_Tool_Project_Exception('"projectDirectory" was supplied but no profile file file was not found at location ' . $projectProfileFilePath);
            }
            $this->_attributes['projectProfileFile'] = $projectProfileFilePath;
        }

        $profileData = file_get_contents($projectProfileFilePath);

        $profileFileParser = new Zend_Tool_Project_Profile_FileParser_Xml();
        $profileFileParser->unserialize($profileData, $this);

        $this->rewind();
    }

    /**
     * storeToFile() - store the current profile to file
     *
     * This will store the profile in memory to a place on disk determined by the attributes
     * available, specifically if the key 'projectProfileFile' is available
     *
     */
    public function storeToFile()
    {
        $file = null;

        if (isset($this->_attributes['projectProfileFile'])) {
            $file = $this->_attributes['projectProfileFile'];
        }

        if ($file == null) {
            #require_once 'Zend/Tool/Project/Exception.php';
            throw new Zend_Tool_Project_Exception('storeToFile() must have a "projectProfileFile" attribute set.');
        }

        $parser = new Zend_Tool_Project_Profile_FileParser_Xml();
        $xml = $parser->serialize($this);
        file_put_contents($file, $xml);
    }

    /**
     * storeToData() - create a string representation of the profile in memory
     *
     * @return string
     */
    public function storeToData()
    {
        $parser = new Zend_Tool_Project_Profile_FileParser_Xml();
        $xml = $parser->serialize($this);
        return $xml;
    }

    /**
     * __toString() - cast this profile to string to be able to view it.
     *
     * @return string
     */
    public function __toString()
    {
        $string = '';
        foreach ($this as $resource) {
            $string .= $resource->getName() . PHP_EOL;
            $rii = new RecursiveIteratorIterator($resource, RecursiveIteratorIterator::SELF_FIRST);
            foreach ($rii as $item) {
                $string .= str_repeat('  ', $rii->getDepth()+1) . $item->getName()
                        . ((count($attributes = $item->getAttributes()) > 0) ? ' [' . http_build_query($attributes) . ']' : '')
                        . PHP_EOL;
            }
        }
        return $string;
    }
}
