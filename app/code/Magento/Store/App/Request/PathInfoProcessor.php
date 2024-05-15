<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\App\Request;

use Magento\Framework\App\Request\PathInfoProcessorInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Processes the path and looks for the store in the url and removes it and modifies the path accordingly.
 */
class PathInfoProcessor implements PathInfoProcessorInterface
{
    /**
     * @var StorePathInfoValidator
     */
    private $storePathInfoValidator;

    /**
     * @param StorePathInfoValidator $storePathInfoValidator
     */
    public function __construct(StorePathInfoValidator $storePathInfoValidator)
    {
        $this->storePathInfoValidator = $storePathInfoValidator;
    }

    /**
     * Process path info and remove store from pathInfo.
     *
     * This method also sets request to no route if store is not valid and store is present in url config is enabled
     *
     * @param RequestInterface $request
     * @param string $pathInfo
     * @return string
     */
    public function process(RequestInterface $request, $pathInfo) : string
    {
        $storeCode = $this->storePathInfoValidator->getValidStoreCode($request, $pathInfo);
        if (!empty($storeCode)) {
            if (!$request->isDirectAccessFrontendName($storeCode)) {
                $pathInfo = $this->trimStoreCodeFromPathInfo($pathInfo, $storeCode);
            } else {
                //no route in case we're trying to access a store that has the same code as a direct access
                $request->setActionName(\Magento\Framework\App\Router\Base::NO_ROUTE);
            }
        }

        return $pathInfo;
    }

    /**
     * Trim store code from path info string if exists
     *
     * @param string $pathInfo
     * @param string $storeCode
     * @return string
     */
    private function trimStoreCodeFromPathInfo(string $pathInfo, string $storeCode) : string
    {
        if (substr($pathInfo, 0, strlen('/' . $storeCode)) == '/'. $storeCode) {
            $pathInfo = substr($pathInfo, strlen($storeCode)+1);
        }
        return empty($pathInfo) ? '/' : $pathInfo;
    }
}
