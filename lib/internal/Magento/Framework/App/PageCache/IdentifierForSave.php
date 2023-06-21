<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\PageCache;

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Page unique identifier
 */
class IdentifierForSave implements IdentifierInterface
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param Http $request
     * @param Context $context
     * @param Json|null $serializer
     */
    public function __construct(
        Http $request,
        Context $context,
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
     */
    public function getValue(): string
    {
        $data = [
            $this->request->isSecure(),
            $this->request->getUriString(),
            $this->context->getVaryString()
        ];

        return sha1($this->serializer->serialize($data));
    }
}
