<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Model\AuthorizenetGateway;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\RuntimeException;

/**
 * Container for request data
 */
class Response extends DataObject
{
    /**
     * The key that will be used to set the root level node name from the response
     */
    const RESPONSE_TYPE = 'response_type';

    /**
     * Populates the response object with the response values from authorize.net
     *
     * @param string $xml
     * @throws RuntimeException
     */
    public function hydrateWithXml(string $xml): void
    {
        if (empty($xml)) {
            throw new RuntimeException(__('Invalid response type.'));
        }

        try {
            $xml = new \SimpleXMLElement($xml);
        } catch (\Exception $e) {
            throw new RuntimeException(__('Invalid response type.'));
        }

        $this->setData(self::RESPONSE_TYPE, $xml->getName());

        $convertChildren = function ($xmlObject, $out = []) use (&$convertChildren) {
            foreach ((array) $xmlObject as $index => $node) {
                $out[$index] = (is_object($node)) ? $convertChildren($node) : $node;
            }

            return $out;
        };

        $data = $convertChildren($xml);
        foreach ($data as $name => $value) {
            $this->setData($name, $value);
        }
    }
}
