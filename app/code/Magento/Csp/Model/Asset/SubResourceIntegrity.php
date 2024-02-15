<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Asset;

/**
 * Class contains function to generate SHA hash of static file contents
 */
class SubResourceIntegrity
{
    /**
     * Constant for Hash algorithm
     */
    private const ALGORITHM = 'sha256';

    /**
     * @var Cache
     */
    private Cache $cache;

    /**
     * constructor
     *
     * @param Cache $cache
     */
    public function __construct(
        Cache $cache
    ) {
        $this->cache = $cache;
    }

    /**
     * Computes hash of file contents for integrity check
     *
     * @param string $content contents of file
     * @return string
     */
    private function getHash(string $content): string
    {
        $hash = hash(self::ALGORITHM, $content, true);
        $base64Hash = base64_encode($hash);
        return self::ALGORITHM . "-{$base64Hash}";
    }

    /**
     * Calculate integrity hash for JS Assets after static content deployment
     *
     * @param string $path
     * @param string $fileContent
     * @return string
     */
    public function generateAssetIntegrity(string $path, string $fileContent): string
    {
        $data = $this->cache->get($path);

        if (is_array($data)) {
            $content = $data['content'];
            $fileIntegrity = $data['integrity'];
            // if content of file changes clean cache and save new value
            if ($content !== $fileContent) {
                $fileIntegrity = $this->getHash($fileContent);
                $this->cache->delete($path);
                //update cache
                $this->cache->save(['integrity' => $fileIntegrity, 'content' => $fileContent], $path);
            }
        } else {
            //cache does not exist create new key value pair
            $fileIntegrity = $this->getHash($fileContent);
            $this->cache->save(['integrity' => $fileIntegrity, 'content' => $fileContent], $path);
        }
        return $fileIntegrity;
    }
}
