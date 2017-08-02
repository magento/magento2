<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rss\Model;

use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\App\Rss\RssManagerInterface;

/**
 * Rss Manager
 *
 * @api
 * @since 2.0.0
 */
class RssManager implements RssManagerInterface
{
    /**
     * @var \Magento\Framework\App\Rss\DataProviderInterface[]
     * @since 2.0.0
     */
    protected $providers;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $dataProviders
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $dataProviders = []
    ) {
        $this->objectManager = $objectManager;
        $this->providers = $dataProviders;
    }

    /**
     * Return Rss Data Provider by Rss Feed Id.
     *
     * @param string $type
     * @return DataProviderInterface
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function getProvider($type)
    {
        if (!isset($this->providers[$type])) {
            throw new \InvalidArgumentException('Unknown provider with type: ' . $type);
        }

        $provider = $this->providers[$type];

        if (is_string($provider)) {
            $provider = $this->objectManager->get($provider);
        }

        if (!$provider instanceof DataProviderInterface) {
            throw new \InvalidArgumentException('Provider should implement DataProviderInterface');
        }

        $this->providers[$type] = $provider;

        return $this->providers[$type];
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getProviders()
    {
        $result = [];
        foreach (array_keys($this->providers) as $type) {
            $result[] = $this->getProvider($type);
        }
        return $result;
    }
}
