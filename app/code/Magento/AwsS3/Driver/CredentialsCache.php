<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Driver;

use Aws\CacheInterface;
use Aws\Credentials\CredentialsFactory;
use Magento\Framework\App\CacheInterface as MagentoCacheInterface;
use Magento\Framework\Serialize\Serializer\Json;

/** Cache Adapter for AWS credentials */
class CredentialsCache implements CacheInterface
{
    /**
     * @var MagentoCacheInterface
     */
    private $magentoCache;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var CredentialsFactory
     */
    private $credentialsFactory;

    /**
     * @param MagentoCacheInterface $magentoCache
     * @param CredentialsFactory $credentialsFactory
     * @param Json $json
     */
    public function __construct(MagentoCacheInterface $magentoCache, CredentialsFactory $credentialsFactory, Json $json)
    {
        $this->magentoCache = $magentoCache;
        $this->credentialsFactory = $credentialsFactory;
        $this->json = $json;
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        $value = $this->magentoCache->load($key);

        if (!is_string($value)) {
            return null;
        }

        $result = $this->json->unserialize($value);
        try {
            return $this->credentialsFactory->create($result);
        } catch (\Exception $e) {
            return $result;
        }
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value, $ttl = 0)
    {
        if (method_exists($value, 'toArray')) {
            $value = $value->toArray();
        }
        $this->magentoCache->save($this->json->serialize($value), $key, [], $ttl);
    }

    /**
     * @inheritdoc
     */
    public function remove($key)
    {
        $this->magentoCache->remove($key);
    }
}
