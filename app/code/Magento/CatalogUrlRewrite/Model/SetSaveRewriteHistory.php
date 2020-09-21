<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model;

use Magento\Framework\Webapi\Rest\Request as RestRequest;

class SetSaveRewriteHistory
{
    private const SAVE_REWRITES_HISTORY = 'save_rewrites_history';

    /**
     * @var RestRequest
     */
    private $request;

    /**
     * @param RestRequest $request
     */
    public function __construct(RestRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Add 'save_rewrites_history' param to the data
     *
     * @param array $result
     * @param string $entityCode
     * @param string $type
     * @return mixed
     */
    public function execute($result, $entityCode, $type)
    {
        $requestBodyParams = $this->request->getBodyParams();

        if ($this->isCustomAttributesExists($requestBodyParams, $entityCode)) {
            foreach ($requestBodyParams[$entityCode]['custom_attributes'] as $attribute) {
                if ($attribute['attribute_code'] === self::SAVE_REWRITES_HISTORY) {
                    foreach ($result as $resultItem) {
                        if ($resultItem instanceof $type) {
                            $resultItem->setData(self::SAVE_REWRITES_HISTORY, (bool)$attribute['value']);
                            break 2;
                        }
                    }
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Check is any custom options exists in data
     *
     * @param array $requestBodyParams
     * @param string $entityCode
     * @return bool
     */
    private function isCustomAttributesExists(array $requestBodyParams, string $entityCode): bool
    {
        return !empty($requestBodyParams[$entityCode]['custom_attributes']);
    }
}
