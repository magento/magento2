<?php
declare(strict_types=1);

namespace Magento\Framework\Api\ExtensionAttribute\InjectorConfig;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use Magento\Framework\Config\ConverterInterface;

class Converter implements ConverterInterface
{
    /**
     * Convert dom node tree to array
     *
     * @param DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $output = [];
        if (!$source instanceof DOMDocument) {
            return $output;
        }

        /** @var DOMNodeList $types */
        $types = $source->getElementsByTagName('extension_attributes');

        /** @var DOMNode $type */
        foreach ($types as $type) {
            $typeConfig = [];
            $typeName = $type->getAttribute('for');

            $injectors = $type->getElementsByTagName('injector');
            foreach ($injectors as $injector) {
                $code = $injector->getAttribute('code');
                $codeType = $injector->getAttribute('type');

                $typeConfig[$code] = $codeType;
            }

            $output[$typeName] = $typeConfig;
        }
        return $output;
    }
}
