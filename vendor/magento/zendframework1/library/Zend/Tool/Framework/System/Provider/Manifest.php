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
 * @see Zend_Tool_Framework_Registry_EnabledInterface
 */
#require_once 'Zend/Tool/Framework/Registry/EnabledInterface.php';

/**
 * @see Zend_Tool_Framework_Provider_Interface
 */
#require_once 'Zend/Tool/Framework/Provider/Interface.php';

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Framework_System_Provider_Manifest
    implements Zend_Tool_Framework_Provider_Interface, Zend_Tool_Framework_Registry_EnabledInterface
{

    public function setRegistry(Zend_Tool_Framework_Registry_Interface $registry)
    {
        $this->_registry = $registry;
    }

    public function getName()
    {
        return 'Manifest';
    }

    public function show()
    {

        $manifestRepository = $this->_registry->getManifestRepository();
        $response           = $this->_registry->getResponse();

        $metadataTree = array();

        $longestAttrNameLen = 50;

        foreach ($manifestRepository as $metadata) {

            $metadataType  = $metadata->getType();
            $metadataName  = $metadata->getName();
            $metadataAttrs = $metadata->getAttributes('attributesParent');

            if (!$metadataAttrs) {
                $metadataAttrs = '(None)';
            } else {
                $metadataAttrs = urldecode(http_build_query($metadataAttrs, null, ', '));
            }

            if (!array_key_exists($metadataType, $metadataTree)) {
                $metadataTree[$metadataType] = array();
            }

            if (!array_key_exists($metadataName, $metadataTree[$metadataType])) {
                $metadataTree[$metadataType][$metadataName] = array();
            }

            if (!array_key_exists($metadataAttrs, $metadataTree[$metadataType][$metadataName])) {
                $metadataTree[$metadataType][$metadataName][$metadataAttrs] = array();
            }

            $longestAttrNameLen = (strlen($metadataAttrs) > $longestAttrNameLen) ? strlen($metadataAttrs) : $longestAttrNameLen;

            $metadataValue = $metadata->getValue();
            if (is_array($metadataValue) && count($metadataValue) > 0) {
                $metadataValue = urldecode(http_build_query($metadataValue, null, ', '));
            } elseif (is_array($metadataValue)) {
                $metadataValue = '(empty array)';
            }

            $metadataTree[$metadataType][$metadataName][$metadataAttrs][] = $metadataValue;
        }

        foreach ($metadataTree as $metadataType => $metadatasByName) {
            $response->appendContent($metadataType);
            foreach ($metadatasByName as $metadataName => $metadatasByAttributes) {
                $response->appendContent("   " . $metadataName);
                foreach ($metadatasByAttributes as $metadataAttributeName => $metadataValues) {
                    foreach ($metadataValues as $metadataValue) {
                        $string = sprintf("      %-{$longestAttrNameLen}.{$longestAttrNameLen}s : ", $metadataAttributeName)
                            . $metadataValue;
                        $response->appendContent($string);
                    }
                }
            }
        }

    }
}
