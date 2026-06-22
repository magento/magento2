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
        $url = preg_replace($pattern, "", (string)$this->request->getUriString());
        list($baseUrl, $query) = $this->reconstructUrl($url);
        $data = [
            $this->request->isSecure(),
            $baseUrl,
            $query,
            $this->request->get(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING)
                ?: $this->context->getVaryString()
        ];
        return sha1($this->serializer->serialize($data));
    }

    /**
     * Pattern detect marketing parameters.
     *
     * Keep in sync with PageCache etc/varnish*.vcl vcl_recv strip logic.
     *
     * @return array
     */
    public function getMarketingParameterPatterns(): array
    {
        return [
            '/&?_branch_match_id\=[^&]+/',
            '/&?_bta_[a-z]+\=[^&]+/',
            '/&?_ga\=[^&]+/',
            '/&?_gl\=[^&]+/',
            '/&?_ke\=[^&]+/',
            '/&?_kx\=[^&]+/',
            '/&?campid\=[^&]+/',
            '/&?ceneo_cid\=[^&]+/',
            '/&?clickId\=[^&]+/',
            '/&?cm\=[^&]+/',
            '/&?cn\=[^&]+/',
            '/&?cof\=[^&]+/',
            '/&?cs\=[^&]+/',
            '/&?customid\=[^&]+/',
            '/&?cx\=[^&]+/',
            '/&?dclid\=[^&]+/',
            '/&?dm_i\=[^&]+/',
            '/&?ef_id\=[^&]+/',
            '/&?epik\=[^&]+/',
            '/&?fbclid\=[^&]+/',
            '/&?gad_[a-z]+\=[^&]+/',
            '/&?gbraid\=[^&]+/',
            '/&?gclid\=[^&]+/',
            '/&?gclsrc\=[^&]+/',
            '/&?gdf[a-z]+\=[^&]+/',
            '/&?hsa_[a-z]+\=[^&]+/',
            '/&?ie\=[^&]+/',
            '/&?igshid\=[^&]+/',
            '/&?mc_[a-z]+\=[^&]+/',
            '/&?mk[a-z]{3}\=[^&]+/',
            '/&?msclkid\=[^&]+/',
            '/&?(?:mtm|matomo)_[a-z]+\=[^&]+/',
            '/&?origin\=[^&]+/',
            '/&?pcrid\=[^&]+/',
            '/&?p(?:iwi)?k_[a-z]+\=[^&]+/',
            '/&?redirect(?:_log)?_mongo_id\=[^&]+/',
            '/&?ref\=[^&]+/',
            '/&?s_kwcid\=[^&]+/',
            '/&?sb_referer_host\=[^&]+/',
            '/&?ScCid\=[^&]+/',
            '/&?si\=[^&]+/',
            '/&?siteurl\=[^&]+/',
            '/&?snrai_[a-z]+\=[^&]+/',
            '/&?srsltid\=[^&]+/',
            '/&?tduid\=[^&]+/',
            '/&?tg\=[^&]+/',
            '/&?trk_[a-z]+\=[^&]+/',
            '/&?utm_[a-z]+\=[^&]+/',
            '/&?wbraid\=[^&]+/',
            '/&?zanpid\=[^&]+/',
        ];
    }

    /**
     * Reconstruct url and sort query
     *
     * @param string $url
     * @return array
     */
    public function reconstructUrl(string $url): array
    {
        if (empty($url)) {
            return [$url, ''];
        }

        $baseUrl = strtok($url, '?');
        $queryString = parse_url($url, PHP_URL_QUERY) ?: '';

        $queryArray = [];
        if ($queryString !== '') {
            parse_str($queryString, $queryArray);
        }

        if (!empty($queryArray)) {
            ksort($queryArray);
            $query = http_build_query($queryArray);
        } else {
            $query = '';
        }

        return [$baseUrl, $query];
    }
}
