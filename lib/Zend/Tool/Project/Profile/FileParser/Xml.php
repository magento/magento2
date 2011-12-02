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
 * @version    $Id: Xml.php 20851 2010-02-02 21:45:51Z ralph $
 */

#require_once 'Zend/Tool/Project/Profile/FileParser/Interface.php';
#require_once 'Zend/Tool/Project/Context/Repository.php';
#require_once 'Zend/Tool/Project/Profile.php';
#require_once 'Zend/Tool/Project/Profile/Resource.php';

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Profile_FileParser_Xml implements Zend_Tool_Project_Profile_FileParser_Interface
{

    /**
     * @var Zend_Tool_Project_Profile
     */
    protected $_profile = null;

    /**
     * @var Zend_Tool_Project_Context_Repository
     */
    protected $_contextRepository = null;

    /**
     * __construct()
     *
     */
    public function __construct()
    {
        $this->_contextRepository = Zend_Tool_Project_Context_Repository::getInstance();
    }

    /**
     * serialize()
     *
     * create an xml string from the provided profile
     *
     * @param Zend_Tool_Project_Profile $profile
     * @return string
     */
    public function serialize(Zend_Tool_Project_Profile $profile)
    {

        $profile = clone $profile;

        $this->_profile = $profile;
        $xmlElement = new SimpleXMLElement('<projectProfile />');

        if ($profile->hasAttribute('type')) {
            $xmlElement->addAttribute('type', $profile->getAttribute('type'));
        }
        
        if ($profile->hasAttribute('version')) {
            $xmlElement->addAttribute('version', $profile->getAttribute('version'));
        }
        
        self::_serializeRecurser($profile, $xmlElement);

        $doc = new DOMDocument('1.0');
        $doc->formatOutput = true;
        $domnode = dom_import_simplexml($xmlElement);
        $domnode = $doc->importNode($domnode, true);
        $domnode = $doc->appendChild($domnode);

        return $doc->saveXML();
    }

    /**
     * unserialize()
     *
     * Create a structure in the object $profile from the structure specficied
     * in the xml string provided
     *
     * @param string xml data
     * @param Zend_Tool_Project_Profile The profile to use as the top node
     * @return Zend_Tool_Project_Profile
     */
    public function unserialize($data, Zend_Tool_Project_Profile $profile)
    {
        if ($data == null) {
            throw new Exception('contents not available to unserialize.');
        }

        $this->_profile = $profile;

        $xmlDataIterator = new SimpleXMLIterator($data);

        if ($xmlDataIterator->getName() != 'projectProfile') {
            throw new Exception('Profiles must start with a projectProfile node');
        }
        
        if (isset($xmlDataIterator['type'])) {
            $this->_profile->setAttribute('type', (string) $xmlDataIterator['type']);
        }
        
        if (isset($xmlDataIterator['version'])) {
            $this->_profile->setAttribute('version', (string) $xmlDataIterator['version']);
        }
        
        // start un-serialization of the xml doc
        $this->_unserializeRecurser($xmlDataIterator);

        // contexts should be initialized after the unwinding of the profile structure
        $this->_lazyLoadContexts();

        return $this->_profile;

    }

    /**
     * _serializeRecurser()
     *
     * This method will be used to traverse the depths of the structure
     * when *serializing* an xml structure into a string
     *
     * @param array $resources
     * @param SimpleXmlElement $xmlNode
     */
    protected function _serializeRecurser($resources, SimpleXmlElement $xmlNode)
    {
        // @todo find a better way to handle concurrency.. if no clone, _position in node gets messed up
        //if ($resources instanceof Zend_Tool_Project_Profile_Resource) {
        //    $resources = clone $resources;
        //}

        foreach ($resources as $resource) {

            if ($resource->isDeleted()) {
                continue;
            }

            $resourceName = $resource->getContext()->getName();
            $resourceName[0] = strtolower($resourceName[0]);

            $newNode = $xmlNode->addChild($resourceName);

            //$reflectionClass = new ReflectionClass($resource->getContext());

            if ($resource->isEnabled() == false) {
                $newNode->addAttribute('enabled', 'false');
            }

            foreach ($resource->getPersistentAttributes() as $paramName => $paramValue) {
                $newNode->addAttribute($paramName, $paramValue);
            }

            if ($resource->hasChildren()) {
                self::_serializeRecurser($resource, $newNode);
            }

        }

    }


    /**
     * _unserializeRecurser()
     *
     * This method will be used to traverse the depths of the structure
     * as needed to *unserialize* the profile from an xmlIterator
     *
     * @param SimpleXMLIterator $xmlIterator
     * @param Zend_Tool_Project_Profile_Resource $resource
     */
    protected function _unserializeRecurser(SimpleXMLIterator $xmlIterator, Zend_Tool_Project_Profile_Resource $resource = null)
    {

        foreach ($xmlIterator as $resourceName => $resourceData) {

            $contextName = $resourceName;
            $subResource = new Zend_Tool_Project_Profile_Resource($contextName);
            $subResource->setProfile($this->_profile);

            if ($resourceAttributes = $resourceData->attributes()) {
                $attributes = array();
                foreach ($resourceAttributes as $attrName => $attrValue) {
                    $attributes[$attrName] = (string) $attrValue;
                }
                $subResource->setAttributes($attributes);
            }

            if ($resource) {
                $resource->append($subResource, false);
            } else {
                $this->_profile->append($subResource);
            }

            if ($this->_contextRepository->isOverwritableContext($contextName) == false) {
                $subResource->initializeContext();
            }

            if ($xmlIterator->hasChildren()) {
                self::_unserializeRecurser($xmlIterator->getChildren(), $subResource);
            }
        }
    }

    /**
     * _lazyLoadContexts()
     *
     * This method will call initializeContext on the resources in a profile
     * @todo determine if this method belongs inside the profile
     *
     */
    protected function _lazyLoadContexts()
    {

        foreach ($this->_profile as $topResource) {
            $rii = new RecursiveIteratorIterator($topResource, RecursiveIteratorIterator::SELF_FIRST);
            foreach ($rii as $resource) {
                $resource->initializeContext();
            }
        }

    }

}