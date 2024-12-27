<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Driver;

use Aws\Credentials\CredentialProvider;

class CachedCredentialsProvider
{
    /**
     * @var CredentialsCache
     */
    private $magentoCacheAdapter;

    /**
     * @param CredentialsCache $magentoCacheAdapter
     */
    public function __construct(CredentialsCache $magentoCacheAdapter)
    {
        $this->magentoCacheAdapter = $magentoCacheAdapter;
    }

    /**
     * Provides cache mechanism to retrieve and store AWS credentials
     *
     * @return callable
     */
    public function get()
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        return call_user_func(
            [CredentialProvider::class, 'cache'],
            //phpcs:ignore Magento2.Functions.DiscouragedFunction
            call_user_func([CredentialProvider::class, 'defaultProvider']),
            $this->magentoCacheAdapter
        );
    }
}
