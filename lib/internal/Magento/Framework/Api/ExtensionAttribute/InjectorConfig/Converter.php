<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\ExtensionAttribute\InjectorConfig;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use Magento\Framework\Config\ConverterInterface;
use Magento\Framework\ObjectManager\ConfigInterface\Proxy as ConfigInterface;

/**
 * Converter class for attributes extensions injection based on XML declarations
 */
class Converter implements ConverterInterface
{
    /**
     * @var ConfigInterface
     */
    private $omConfig;

    /**
     * @param ConfigInterface $omConfig
     */
    public function __construct(ConfigInterface $omConfig)
    {
        $this->omConfig = $omConfig;
    }

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

            if (!empty($injectors)) {
                foreach ($injectors as $injector) {
                    $code = $injector->getAttribute('code');
                    $type = $injector->getAttribute('type');

                    $typeConfig[$code] = $type;
                }

                $output[ltrim($typeName, '\\')] = $typeConfig;

                $preferenceFor = $this->omConfig->getPreference($typeName);
                if ($preferenceFor !== $typeName) {
                    $output[ltrim($preferenceFor, '\\')] = $typeConfig;
                }
            }
        }

        return $output;
    }
}
