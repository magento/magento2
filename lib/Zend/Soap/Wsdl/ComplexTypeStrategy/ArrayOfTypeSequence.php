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

use Zend\Soap\Wsdl;

/**
 * Zend_Soap_Wsdl_Strategy_ArrayOfTypeSequence
 *
 * @category   Zend
 * @package    Zend_Soap
 * @subpackage WSDL
 */
class ArrayOfTypeSequence extends DefaultComplexType
{
    /**
     * Add an unbounded ArrayOfType based on the xsd:sequence syntax if type[] is detected in return value doc comment.
     *
     * @param string $type
     * @return string tns:xsd-type
     */
    public function addComplexType($type)
    {
        $nestedCounter = $this->_getNestedCount($type);

        if ($nestedCounter > 0) {
            $singularType = $this->_getSingularType($type);

            for ($i = 1; $i <= $nestedCounter; $i++) {
                $complexType    = $this->_getTypeBasedOnNestingLevel($singularType, $i);
                $complexTypePhp = $singularType . str_repeat('[]', $i);
                $childType      = $this->_getTypeBasedOnNestingLevel($singularType, $i-1);

                $this->_addSequenceType($complexType, $childType, $complexTypePhp);
            }

            return $complexType;
        } elseif (($soapType = $this->scanRegisteredTypes($type)) !== null) {
            // Existing complex type
            return $soapType;
        } else {
            // New singular complex type
            return parent::addComplexType($type);
        }
    }

    /**
     * Return the ArrayOf or simple type name based on the singular xsdtype and the nesting level
     *
     * @param  string $singularType
     * @param  int    $level
     * @return string
     */
    protected function _getTypeBasedOnNestingLevel($singularType, $level)
    {
        if ($level == 0) {
            // This is not an Array anymore, return the xsd simple type
            return $this->getContext()->getType($singularType);
        } else {
            return 'tns:' . str_repeat('ArrayOf', $level) . ucfirst($this->getContext()->translateType($singularType));
        }
    }

    /**
     * From a nested definition with type[], get the singular xsd:type
     *
     * @param  string $type
     * @return string
     */
    protected function _getSingularType($type)
    {
        return str_replace('[]', '', $type);
    }

    /**
     * Return the array nesting level based on the type name
     *
     * @param  string $type
     * @return integer
     */
    protected function _getNestedCount($type)
    {
        return substr_count($type, '[]');
    }

    /**
     * Append the complex type definition to the WSDL via the context access
     *
     * @param  string $arrayType      Array type name (e.g. 'tns:ArrayOfArrayOfInt')
     * @param  string $childType      Qualified array items type (e.g. 'xsd:int', 'tns:ArrayOfInt')
     * @param  string $phpArrayType   PHP type (e.g. 'int[][]', '\MyNamespace\MyClassName[][][]')
     * @return void
     */
    protected function _addSequenceType($arrayType, $childType, $phpArrayType)
    {
        if ($this->scanRegisteredTypes($phpArrayType) !== null) {
            return;
        }

        // Register type here to avoid recursion
        $this->getContext()->addType($phpArrayType, $arrayType);


        $dom = $this->getContext()->toDomDocument();

        $arrayTypeName = substr($arrayType, strpos($arrayType, ':') + 1);

        $complexType = $dom->createElement('xsd:complexType');
        $complexType->setAttribute('name', $arrayTypeName);

        $sequence = $dom->createElement('xsd:sequence');

        $element = $dom->createElement('xsd:element');
        $element->setAttribute('name', 'item');
        $element->setAttribute('type', $childType);
        $element->setAttribute('minOccurs', 0);
        $element->setAttribute('maxOccurs', 'unbounded');
        $sequence->appendChild($element);

        $complexType->appendChild($sequence);

        $this->getContext()->getSchema()->appendChild($complexType);
    }
}
