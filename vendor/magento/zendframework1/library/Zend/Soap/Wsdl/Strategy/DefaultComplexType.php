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
 * @package    Zend_Soap
 * @subpackage Wsdl
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Soap_Wsdl_Strategy_Abstract
 */
#require_once "Zend/Soap/Wsdl/Strategy/Abstract.php";

/**
 * Zend_Soap_Wsdl_Strategy_DefaultComplexType
 *
 * @category   Zend
 * @package    Zend_Soap
 * @subpackage Wsdl
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Soap_Wsdl_Strategy_DefaultComplexType extends Zend_Soap_Wsdl_Strategy_Abstract
{
    /**
     * Add a complex type by recursivly using all the class properties fetched via Reflection.
     *
     * @param  string $type Name of the class to be specified
     * @return string XSD Type for the given PHP type
     */
    public function addComplexType($type)
    {
        if(!class_exists($type)) {
            #require_once "Zend/Soap/Wsdl/Exception.php";
            throw new Zend_Soap_Wsdl_Exception(sprintf(
                "Cannot add a complex type %s that is not an object or where ".
                "class could not be found in 'DefaultComplexType' strategy.", $type
            ));
        }

        $dom = $this->getContext()->toDomDocument();
        $class = new ReflectionClass($type);

        $defaultProperties = $class->getDefaultProperties();

        $complexType = $dom->createElement('xsd:complexType');
        $complexType->setAttribute('name', $type);

        $all = $dom->createElement('xsd:all');

        foreach ($class->getProperties() as $property) {
            if ($property->isPublic() && preg_match_all('/@var\s+([^\s]+)/m', $property->getDocComment(), $matches)) {

                /**
                 * @todo check if 'xsd:element' must be used here (it may not be compatible with using 'complexType'
                 * node for describing other classes used as attribute types for current class
                 */
                $element = $dom->createElement('xsd:element');
                $element->setAttribute('name', $propertyName = $property->getName());
                $element->setAttribute('type', $this->getContext()->getType(trim($matches[1][0])));

                // If the default value is null, then this property is nillable.
                if ($defaultProperties[$propertyName] === null) {
                    $element->setAttribute('nillable', 'true');
                }

                $all->appendChild($element);
            }
        }

        $complexType->appendChild($all);
        $this->getContext()->getSchema()->appendChild($complexType);
        $this->getContext()->addType($type);

        return "tns:$type";
    }
}
