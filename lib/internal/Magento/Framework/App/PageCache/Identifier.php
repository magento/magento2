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
 */
class Identifier implements IdentifierInterface
{
    /**
     * Pattern detect marketing parameters
     */
    public const PATTERN_MARKETING_PARAMETERS = [
        '/&?gclid\=[^&]+/',
        '/&?cx\=[^&]+/',
        '/&?ie\=[^&]+/',
        '/&?cof\=[^&]+/',
        '/&?siteurl\=[^&]+/',
        '/&?zanpid\=[^&]+/',
        '/&?origin\=[^&]+/',
        '/&?fbclid\=[^&]+/',
        '/&?mc_(.*?)\=[^&]+/',
        '/&?utm_(.*?)\=[^&]+/',
        '/&?_bta_(.*?)\=[^&]+/',
    ];

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $context;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Http\Context $context
     * @param Json|null $serializer
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
     */
    public function getValue()
    {
        $pattern = self::PATTERN_MARKETING_PARAMETERS;
        $replace = array_fill(0, count(self::PATTERN_MARKETING_PARAMETERS), '');
        $data = [
            $this->request->isSecure(),
            preg_replace($pattern, $replace, (string)$this->request->getUriString()),
            $this->request->get(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING)
                ?: $this->context->getVaryString()
        ];

        return sha1($this->serializer->serialize($data));
    }
}
