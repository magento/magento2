<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Convert;

use Magento\Framework\Exception\LocalizedException;

/**
 * Convert the array data to SimpleXMLElement object
 */
class ConvertArray
{
    /**
     * Transform an assoc array to \SimpleXMLElement object
     * Array has some limitations. Appropriate exceptions will be thrown
     *
     * @param array $array
     * @param string $rootName
     * @return \SimpleXMLElement
     * @throws LocalizedException
     */
    public function assocToXml(array $array, $rootName = '_')
    {
        if (empty($rootName) || is_numeric($rootName)) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase(
                    "The root element can't be empty or use numbers. Change the element and try again."
                )
            );
        }

        $xmlStr = <<<XML
<?xml version='1.0' encoding='UTF-8' standalone='yes'?>
<$rootName></$rootName>
XML;
        $xml = new \SimpleXMLElement($xmlStr);
        foreach (array_keys($array) as $key) {
            if (is_numeric($key)) {
                throw new LocalizedException(
                    new \Magento\Framework\Phrase('An error occurred. Use non-numeric array root keys and try again.')
                );
            }
        }
        return self::_assocToXml($array, $rootName, $xml);
    }

    /**
     * Convert nested array into flat array.
     *
     * @param array $data
     * @return array
     */
    public static function toFlatArray($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = self::toFlatArray($value);
                unset($data[$key]);
                $data = array_merge($data, $value);
            }
        }
        return $data;
    }

    /**
     * Function, that actually recursively transforms array to xml
     *
     * @param array $array
     * @param string $rootName
     * @param \SimpleXMLElement $xml
     * @return \SimpleXMLElement
     * @throws LocalizedException
     */
    private function _assocToXml(array $array, $rootName, \SimpleXMLElement $xml)
    {
        $hasNumericKey = false;
        $hasStringKey = false;
        foreach ($array as $key => $value) {
            if (!is_array($value)) {
                if (is_string($key)) {
                    if ($key === $rootName) {
                        throw new LocalizedException(
                            new \Magento\Framework\Phrase(
                                "An associative key can't be the same as its parent associative key. "
                                . "Verify and try again."
                            )
                        );
                    }
                    $hasStringKey = true;
                    $xml->addChild($key, $value);
                } elseif (is_int($key)) {
                    $hasNumericKey = true;
                    $xml->addChild($key, $value);
                }
            } else {
                $xml->addChild($key);
                self::_assocToXml($value, $key, $xml->{$key});
            }
        }
        if ($hasNumericKey && $hasStringKey) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase(
                    "Associative and numeric keys can't be mixed at one level. Verify and try again."
                )
            );
        }
        return $xml;
    }
}
