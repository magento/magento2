<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
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
        ?Json $serializer = null
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
        $pattern = $this->getMarketingParameterPatterns();
        $replace = array_fill(0, count($pattern), '');
        $data = [
            $this->request->isSecure(),
            preg_replace($pattern, $replace, (string)$this->request->getUriString()),
            $this->request->get(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING)
                ?: $this->context->getVaryString()
        ];

        return sha1($this->serializer->serialize($data));
    }

    /**
     * Pattern detect marketing parameters
     *
     * @return array
     */
    public function getMarketingParameterPatterns(): array
    {
        return [
            '/&?gad_source\=[^&]+/',
            '/&?gbraid\=[^&]+/',
            '/&?wbraid\=[^&]+/',
            '/&?_gl\=[^&]+/',
            '/&?dclid\=[^&]+/',
            '/&?gclsrc\=[^&]+/',
            '/&?srsltid\=[^&]+/',
            '/&?msclkid\=[^&]+/',
            '/&?_kx\=[^&]+/',
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
    }
}
