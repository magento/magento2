<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Model\App;

class PageCachePlugin
{
    /**
     * Label for compressed cache entries
     */
    const COMPRESSION_PREFIX = 'COMPRESSED_CACHE_';

    /**
     * Enable type management by adding type tag, and enable cache compression
     *
     * @param \Magento\Framework\App\PageCache\Cache $subject
     * @param string $data
     * @param string $identifier
     * @param string[] $tags
     * @param int|null $lifeTime
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Framework\App\PageCache\Cache $subject,
        $data,
        $identifier,
        $tags = [],
        $lifeTime = null
    ) {
        $data = $this->handleCompression($data);
        $tags[] = \Magento\PageCache\Model\Cache\Type::CACHE_TAG;
        return [$data, $identifier, $tags, $lifeTime];
    }

    /**
     * Enable cache de-compression
     *
     * @param \Magento\Framework\App\PageCache\Cache $subject
     * @param string $result
     * @return string|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterLoad(
        \Magento\Framework\App\PageCache\Cache $subject,
        $result
    ) {
        if ($result && strpos($result, self::COMPRESSION_PREFIX) === 0) {
            $result = function_exists('gzuncompress')
                ? gzuncompress(substr($result, strlen(self::COMPRESSION_PREFIX)))
                : false;
        }
        return $result;
    }

    /**
     * Label compressed entries and check if gzcompress exists
     *
     * @param string $data
     * @return string
     */
    private function handleCompression($data)
    {
        if (function_exists('gzcompress')) {
            $data = self::COMPRESSION_PREFIX . gzcompress($data);
        }
        return $data;
    }
}
