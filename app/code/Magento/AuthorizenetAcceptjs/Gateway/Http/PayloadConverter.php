<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Http;

use Magento\Framework\Exception\RuntimeException;

/**
 * Helper for converting payloads to and from authorize.net xml
 */
class PayloadConverter
{
    /**
     * The key that will be used to determine the root level type in the xml body
     */
    const PAYLOAD_TYPE = 'payload_type';

    /**
     * Returns XML string payload compatible with the Authorize.net API
     *
     * @param array $data
     * @return string
     * @throws RuntimeException
     */
    public function convertArrayToXml(array $data): string
    {
        if (empty($data[self::PAYLOAD_TYPE])) {
            throw new RuntimeException(__('Invalid payload type.'));
        }
        $requestType = $data[self::PAYLOAD_TYPE];
        unset($data[self::PAYLOAD_TYPE]);

        $convertFields = function ($data) use (&$convertFields) {
            $xml = '';
            foreach ($data as $fieldName => $fieldValue) {
                $value = (is_array($fieldValue) ? $convertFields($fieldValue) : htmlspecialchars($fieldValue));
                $xml .= '<' . $fieldName . '>' . $value  . '</' . $fieldName . '>';
            }
            return $xml;
        };

        return '<' . $requestType . ' xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">' . $convertFields($data) . '</' . $requestType . '>';
    }

    /**
     * Populates the response object with the response values from authorize.net
     *
     * @param string $xml
     * @return array
     * @throws RuntimeException
     */
    public function convertXmlToArray(string $xml): array
    {
        if (empty($xml)) {
            throw new RuntimeException(__('Invalid payload type.'));
        }

        try {
            $xml = simplexml_load_string($xml,\SimpleXMLElement::class, \LIBXML_NOWARNING);
        } catch (\Exception $e) {
            throw new RuntimeException(__('Invalid payload type.'));
        }

        $convertChildren = function ($xmlObject, $out = []) use (&$convertChildren) {
            foreach ((array)$xmlObject as $index => $node) {
                if (is_array($node)) {
                    $out[$index] = [];

                    foreach ($node as $subnode) {
                        $out[$index][] = (is_object($subnode) ? $convertChildren($subnode) : $subnode);
                    }
                }
                else {
                    $out[$index] = (is_object($node)) ? $convertChildren($node) : $node;
                }
            }

            return $out;
        };

        $data = $convertChildren($xml);
        $data[self::PAYLOAD_TYPE] = $xml->getName();

        return $data;
    }
}
