<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp;

use Magento\Framework\MessageQueue\ExchangeFactoryInterface;
use Magento\Framework\ObjectManagerInterface;

class ExchangeFactory implements ExchangeFactoryInterface
{
    /**
     * @param ObjectManagerInterface $objectManager
     * @param StompClientProvider $stompClientProvider
     * @param string $instanceName
     */
    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        private readonly StompClientProvider $stompClientProvider,
        private readonly string $instanceName = Exchange::class
    ) {
    }

    /**
     * @inheritdoc
     */
    public function create($connectionName, array $data = [])
    {
        $data['stompClient'] = $this->stompClientProvider->get($connectionName);
        return $this->objectManager->create($this->instanceName, $data);
    }
}
