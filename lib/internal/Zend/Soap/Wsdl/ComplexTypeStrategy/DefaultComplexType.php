<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Soap
 */

namespace Zend\Soap\Wsdl\ComplexTypeStrategy;

use Zend\Soap\Exception;

/**
 * Zend_Soap_Wsdl_Strategy_DefaultComplexType
 *
 * @category   Zend
 * @package    Zend_Soap
 * @subpackage WSDL
 */
class DefaultComplexType extends AbstractComplexTypeStrategy
{
    /**
     * Add a complex type by recursivly using all the class properties fetched via Reflection.
     *
     * @param  string $type Name of the class to be specified
     * @throws Exception\InvalidArgumentException if class does not exist
     * @return string XSD Type for the given PHP type
     */
    public function addComplexType($type)
    {
        if (!class_exists($type)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Cannot add a complex type %s that is not an object or where '
              . 'class could not be found in \'DefaultComplexType\' strategy.', $type
            ));
        }

        if (($soapType = $this->scanRegisteredTypes($type)) !== null) {
            return $soapType;
        }

        $dom = $this->getContext()->toDomDocument();
        $class = new \ReflectionClass($type);

        $soapTypeName = $this->getContext()->translateType($type);
        $soapType     = 'tns:' . $soapTypeName;

        // Register type here to avoid recursion
        $this->getContext()->addType($type, $soapType);


        $defaultProperties = $class->getDefaultProperties();

        $complexType = $dom->createElement('xsd:complexType');
        $complexType->setAttribute('name', $soapTypeName);

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

        return $soapType;
    }
}
