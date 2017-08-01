<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\PageCache;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Page unique identifier
 * @since 2.0.0
 */
class Identifier
{
    /**
     * @var \Magento\Framework\App\Request\Http
     * @since 2.0.0
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Http\Context
     * @since 2.0.0
     */
    protected $context;

    /**
     * @var Json
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Http\Context $context
     * @param Json|null $serializer
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Http\Context $context,
        Json $serializer = null
    ) {
        $this->request = $request;
        $this->context = $context;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Return unique page identifier
     *
     * @return string
     * @since 2.0.0
     */
    public function getValue()
    {
        $data = [
            $this->request->isSecure(),
            $this->request->getUriString(),
            $this->request->get(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING)
                ?: $this->context->getVaryString()
        ];
        return sha1($this->serializer->serialize($data));
    }
}
