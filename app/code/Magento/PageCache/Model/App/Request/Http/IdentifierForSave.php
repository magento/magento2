<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Model\App\Request\Http;

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\PageCache\IdentifierInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Page unique identifier
 */
class IdentifierForSave implements IdentifierInterface
{
    /**
     * @param Http $request
     * @param Context $context
     * @param Json $serializer
     * @param IdentifierStoreReader $identifierStoreReader
     */
    public function __construct(
        private Http                  $request,
        private Context               $context,
        private Json                  $serializer,
        private IdentifierStoreReader $identifierStoreReader,
    ) {
    }

    /**
     * Return unique page identifier
     *
     * @return string
     */
    public function getValue()
    {
        $url = (string)$this->request->getUriString();
        list($baseUrl, $query) = $this->reconstructUrl($url);
        $data = [
            $this->request->isSecure(),
            $baseUrl,
            $query,
            $this->context->getVaryString()
        ];

        $data = $this->identifierStoreReader->getPageTagsWithStoreCacheTags($data);
        return sha1($this->serializer->serialize($data));
    }

    /**
     * Reconstruct url and sort query
     *
     * @param string $url
     * @return array
     */
    private function reconstructUrl(string $url): array
    {
        if (empty($url)) {
            return [$url, ''];
        }
        $baseUrl = strtok($url, '?');
        $query = $this->request->getUri()->getQueryAsArray();
        if (!empty($query)) {
            ksort($query);
            $query = http_build_query($query);
        } else {
            $query = '';
        }
        return [$baseUrl, $query];
    }
}
