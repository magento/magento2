<?php
/**
 * @see       https://github.com/laminas/laminas-soap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-soap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-soap/blob/master/LICENSE.md New BSD License
 */

namespace Magento\Webapi\Model\Laminas\Soap\ComplexTypeStrategy;

use Magento\Webapi\Model\Laminas\Soap\Wsdl;

class ArrayOfTypeSequence extends DefaultComplexType
{
    /**
     * @inheritdoc
     */
    public function addComplexType($type)
    {
        $nestedCounter = $this->getNestedCount($type);

        if ($nestedCounter > 0) {
            $singularType = $this->getSingularType($type);
            $complexType = '';

            for ($i = 1; $i <= $nestedCounter; $i++) {
                $complexType    = $this->getTypeBasedOnNestingLevel($singularType, $i);
                $complexTypePhp = $singularType . str_repeat('[]', $i);
                $childType      = $this->getTypeBasedOnNestingLevel($singularType, $i - 1);

                $this->addSequenceType($complexType, $childType, $complexTypePhp);
            }

            return $complexType;
        }

        if (($soapType = $this->scanRegisteredTypes($type)) !== null) {
            // Existing complex type
            return $soapType;
        }

        // New singular complex type
        return parent::addComplexType($type);
    }

    /**
     * Return the ArrayOf or simple type name based on the singular xsdtype
     * and the nesting level
     *
     * @param  string $singularType
     * @param  int    $level
     * @return string
     */
    protected function getTypeBasedOnNestingLevel($singularType, $level)
    {
        if ($level == 0) {
            // This is not an Array anymore, return the xsd simple type
            return $this->getContext()->getType($singularType);
        }

        return Wsdl::TYPES_NS
            . ':'
            . str_repeat('ArrayOf', $level)
            . ucfirst($this->getContext()->translateType($singularType));
    }

    /**
     * From a nested definition with type[], get the singular xsd:type
     *
     * @param  string $type
     * @return string
     */
    protected function getSingularType($type)
    {
        return str_replace('[]', '', $type);
    }

    /**
     * Return the array nesting level based on the type name
     *
     * @param  string $type
     * @return int
     */
    protected function getNestedCount($type)
    {
        return substr_count($type, '[]');
    }

    /**
     * Append the complex type definition to the WSDL via the context access
     *
     * @param  string $arrayType      Array type name (e.g. 'tns:ArrayOfArrayOfInt')
     * @param  string $childType      Qualified array items type (e.g. 'xsd:int', 'tns:ArrayOfInt')
     * @param  string $phpArrayType   PHP type (e.g. 'int[][]', '\MyNamespace\MyClassName[][][]')
     */
    protected function addSequenceType($arrayType, $childType, $phpArrayType)
    {
        if ($this->scanRegisteredTypes($phpArrayType) !== null) {
            return;
        }

        // Register type here to avoid recursion
        $this->getContext()->addType($phpArrayType, $arrayType);

        $dom = $this->getContext()->toDomDocument();

        $arrayTypeName = substr($arrayType, strpos($arrayType, ':') + 1);

        $complexType = $dom->createElementNS(Wsdl::XSD_NS_URI, 'complexType');
        $this->getContext()->getSchema()->appendChild($complexType);

        $complexType->setAttribute('name', $arrayTypeName);

        $sequence = $dom->createElementNS(Wsdl::XSD_NS_URI, 'sequence');
        $complexType->appendChild($sequence);

        $element = $dom->createElementNS(Wsdl::XSD_NS_URI, 'element');
        $sequence->appendChild($element);

        $element->setAttribute('name', 'item');
        $element->setAttribute('type', $childType);
        $element->setAttribute('minOccurs', 0);
        $element->setAttribute('maxOccurs', 'unbounded');
    }
}
