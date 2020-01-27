<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Plugin\App\PageCache;

/**
 * Handles unique identifier for graphql query
 */
class Identifier
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $context;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var \Magento\PageCache\Model\Config
     */
    private $config;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Http\Context $context
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param \Magento\PageCache\Model\Config $config
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Http\Context $context,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Magento\PageCache\Model\Config $config
    ) {
        $this->request = $request;
        $this->context = $context;
        $this->serializer = $serializer;
        $this->config = $config;
    }

    /**
     * Add/Override a unique key identifier for graphql specific query and variables that skips X-Magento-Vary cookie
     *
     * @param \Magento\Framework\App\PageCache\Identifier $identifier
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetValue(\Magento\Framework\App\PageCache\Identifier $identifier, string $result) : string
    {
        if ($this->config->isEnabled()) {
            $data = [
                $this->request->isSecure(),
                $this->request->getUriString(),
                $this->context->getVaryString()
            ];
            $result = sha1($this->serializer->serialize($data));
        }
        return $result;
    }
}
