<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\App\Request;

/**
 * Processes the path and looks for the store in the url and removes it and modifies the path accordingly.
 */
class PathInfoProcessor implements \Magento\Framework\App\Request\PathInfoProcessorInterface
{
    /**
     * @var StorePathInfoValidator
     */
    private $storePathInfoValidator;

    /**
     * @param \Magento\Store\App\Request\StorePathInfoValidator $storePathInfoValidator
     */
    public function __construct(
        \Magento\Store\App\Request\StorePathInfoValidator $storePathInfoValidator
    ) {
        $this->storePathInfoValidator = $storePathInfoValidator;
    }

    /**
     * Process path info and remove store from pathInfo
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $pathInfo
     * @return string
     */
    public function process(\Magento\Framework\App\RequestInterface $request, $pathInfo) : string
    {
        $trimmedPathInfo = $this->storePathInfoValidator->trimValidStoreFromPathInfo($request, $pathInfo);
        return $trimmedPathInfo ? : $pathInfo;
    }
}
