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
class Request extends DataObject
{
    /**
     * The key that will be used to determine the root level request type in the request body
     */
    const REQUEST_TYPE = 'request_type';

    /**
     * Returns XML string version of this request ready to send to Authorize.net
     *
     * @return string
     * @throws RuntimeException
     */
    public function toApiXml(): string
    {
        $data = $this->toArray();
        if (empty($data[self::REQUEST_TYPE])) {
            throw new RuntimeException(__('Invalid request type.'));
        }
        $requestType = $data[self::REQUEST_TYPE];
        unset($data[self::REQUEST_TYPE]);

        $convertFields = function ($data) use (&$convertFields) {
            $xml = '';
            foreach ($data as $fieldName => $fieldValue) {
                $value = (is_array($fieldValue) ? $convertFields($fieldValue) : htmlspecialchars($fieldValue));
                $xml .= '<' . $fieldName . '>' . $value  . '</' . $fieldName . '>';
            }
            return $xml;
        };

        return '<' . $requestType . '>' . $convertFields($data) . '</' . $requestType . '>';
    }
}
