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
            throw new LocalizedException(new \Magento\Framework\Phrase('Root element must not be empty or numeric'));
        }

        $xmlStr = <<<XML
<?xml version='1.0' encoding='UTF-8' standalone='yes'?>
<$rootName></$rootName>
XML;
        $xml = new \SimpleXMLElement($xmlStr);
        foreach (array_keys($array) as $key) {
            if (is_numeric($key)) {
                throw new LocalizedException(new \Magento\Framework\Phrase('Array root keys must not be numeric.'));
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
                                'Associative key must not be the same as its parent associative key.'
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
                new \Magento\Framework\Phrase('Associative and numeric keys must not be mixed at one level.')
            );
        }
        return $xml;
    }
}
