<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

class StompClientProvider implements ResetAfterRequestInterface
{
    /**
     * @var StompClientInterface[]
     */
    private array $stompClients = [];

    /**
     * @param ConfigPool $configPool
     * @param StompClientFactory $stompClientFactory
     */
    public function __construct(
        private readonly ConfigPool $configPool,
        private readonly StompClientFactory $stompClientFactory,
    ) {
    }

    /**
     * Get stomp client by connection name.
     *
     * @param string $connectionName
     * @return StompClientInterface
     */
    public function get(string $connectionName = Config::STOMP_CONFIG): StompClientInterface
    {
        if (!isset($this->stompClients[$connectionName])) {
            $stompConfig = $this->configPool->get($connectionName);
            $this->stompClients[$connectionName] = $this->stompClientFactory->create(['stompConfig' => $stompConfig]);
        }

        return $this->stompClients[$connectionName];
    }

    /**
     * @inheritdoc
     */
    public function _resetState(): void
    {
        $this->stompClients = [];
    }
}
