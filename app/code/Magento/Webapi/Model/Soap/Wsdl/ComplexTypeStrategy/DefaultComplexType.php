<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Model\Soap\Wsdl\ComplexTypeStrategy;

use DOMElement;
use InvalidArgumentException;
use Magento\Webapi\Model\Soap\Wsdl;
use Magento\Webapi\Api\Data\ComplexTypeStrategy\DocumentationStrategyInterface;
use ReflectionClass;
use ReflectionProperty;

/**
 * Class DefaultComplexType
 */
class DefaultComplexType extends AbstractComplexTypeStrategy
{
    /**
     * @inheritDoc
     */
    public function addComplexType(string $type): string
    {
        if (!class_exists($type)) {
            throw new InvalidArgumentException(
                sprintf(
                'Cannot add a complex type %s that is not an object or where '
                . 'class could not be found in "DefaultComplexType" strategy.',
                $type
            ));
        }

        $class   = new ReflectionClass($type);
        $phpType = $class->getName();

        if (($soapType = $this->scanRegisteredTypes($phpType)) !== null) {
            return $soapType;
        }

        $dom = $this->getContext()->toDomDocument();
        $soapTypeName = $this->getContext()->translateType($phpType);
        $soapType     = Wsdl::TYPES_NS . ':' . $soapTypeName;

        // Register type here to avoid recursion
        $this->getContext()->addType($phpType, $soapType);

        $defaultProperties = $class->getDefaultProperties();

        $complexType = $dom->createElementNS(Wsdl::XSD_NS_URI, 'complexType');
        $complexType->setAttribute('name', $soapTypeName);

        $all = $dom->createElementNS(Wsdl::XSD_NS_URI, 'all');

        foreach ($class->getProperties() as $property) {
            if ($property->isPublic() && preg_match_all('/@var\s+([^\s]+)/m', $property->getDocComment(), $matches)) {
                $element = $dom->createElementNS(Wsdl::XSD_NS_URI, 'element');
                $element->setAttribute('name', $propertyName = $property->getName());
                $element->setAttribute('type', $this->getContext()->getType(trim($matches[1][0])));

                // If the default value is null, then this property is nillable.
                if ($defaultProperties[$propertyName] === null) {
                    $element->setAttribute('nillable', 'true');
                }

                $this->addPropertyDocumentation($property, $element);
                $all->appendChild($element);
            }
        }

        $complexType->appendChild($all);
        $this->addComplexTypeDocumentation($class, $complexType);
        $this->getContext()->getSchema()->appendChild($complexType);

        return $soapType;
    }

    /**
     * This Method add property documentation.
     *
     * @param ReflectionProperty $property
     * @param DOMElement $element
     *
     * @return void
     */
    private function addPropertyDocumentation(ReflectionProperty $property, DOMElement $element): void
    {
        if ($this->documentationStrategy instanceof DocumentationStrategyInterface) {
            $documentation = $this->documentationStrategy->getPropertyDocumentation($property);

            if ($documentation) {
                $this->getContext()->addDocumentation($element, $documentation);
            }
        }
    }

    /**
     * This Method add complex type documentation.
     *
     * @param ReflectionClass $class
     * @param DOMElement $element
     *
     * @return void
     */
    private function addComplexTypeDocumentation(ReflectionClass $class, DOMElement $element): void
    {
        if ($this->documentationStrategy instanceof DocumentationStrategyInterface) {
            $documentation = $this->documentationStrategy->getComplexTypeDocumentation($class);

            if ($documentation) {
                $this->getContext()->addDocumentation($element, $documentation);
            }
        }
    }
}
