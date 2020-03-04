<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Csp\Model\Collector\CspWhitelistXml;

use Magento\Framework\Config\ConverterInterface;

/**
 * Converts csp_whitelist.xml files' content into config data.
 */
class Converter implements ConverterInterface
{
    /**
     * @inheritDoc
     */
    public function convert($source)
    {
        $policyConfig = [];

        /** @var \DOMNodeList $policies */
        $policies = $source->getElementsByTagName('policy');
        /** @var \DOMElement $policy */
        foreach ($policies as $policy) {
            if ($policy->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $id = $policy->attributes->getNamedItem('id')->nodeValue;
            if (!array_key_exists($id, $policyConfig)) {
                $policyConfig[$id] = ['hosts' => [], 'hashes' => []];
            }
            /** @var \DOMElement $value */
            foreach ($policy->getElementsByTagName('value') as $value) {
                if ($value->attributes->getNamedItem('type')->nodeValue === 'host') {
                    $policyConfig[$id]['hosts'][] = $value->nodeValue;
                } else {
                    $policyConfig[$id]['hashes'][$value->nodeValue]
                        = $value->attributes->getNamedItem('algorithm')->nodeValue;
                }
            }
            $policyConfig[$id]['hosts'] = array_unique($policyConfig[$id]['hosts']);
        }

        return $policyConfig;
    }
}
