<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsyncConfig\Model;

use Magento\AsyncConfig\Api\Data\AsyncConfigMessageInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Consumer
{
    /**
     * Backend Config Model Factory
     *
     * @var \Magento\Config\Model\Config\Factory
     */
    private $configFactory;

    /**
     * @var Json
     */
    private $serializer;

    /**
     *
     * @param \Magento\Config\Model\Config\Factory $configFactory
     * @param Json $json
     */
    public function __construct(
        \Magento\Config\Model\Config\Factory $configFactory,
        Json $json
    ) {
        $this->configFactory = $configFactory;
        $this->serializer = $json;
    }
    /**
     * Process Consumer
     *
     * @param AsyncConfigMessageInterface $asyncConfigMessage
     * @return void
     * @throws \Exception
     */
    public function process(AsyncConfigMessageInterface $asyncConfigMessage): void
    {
        $configData = $asyncConfigMessage->getConfigData();
        $data = $this->serializer->unserialize($configData);
        /** @var \Magento\Config\Model\Config $configModel */
        $configModel = $this->configFactory->create(['data' => $data]);
        $configModel->save();
    }
}
