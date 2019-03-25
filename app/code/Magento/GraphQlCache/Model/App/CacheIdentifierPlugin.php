<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCache\Model\App;

/**
 * Class CachePlugin
 * Should add unique identifier for graphql query
 */
class CacheIdentifierPlugin
{
    /**
     * Constructor
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\PageCache\Model\Config $config
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\PageCache\Model\Config $config
    ) {
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * Adds a unique key identifier for graphql specific query and variables
     *
     * @param \Magento\Framework\App\PageCache\Identifier $identifier
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetValue(\Magento\Framework\App\PageCache\Identifier $identifier, $result)
    {
        //If full page cache is enabled
        if ($this->config->isEnabled()) {
            //we need to compute unique query identifier from the 3 variables and removing whitespaces
            $data = [
                $this->request->isSecure(),
                $this->request->getUriString(),
                $this->request->get(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING)
                    ?: $this->context->getVaryString()
            ];
            $result = sha1($this->serializer->serialize($data));
        }
        return $result;
    }
}
