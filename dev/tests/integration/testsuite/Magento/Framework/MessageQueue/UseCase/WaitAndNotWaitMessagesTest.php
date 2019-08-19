<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\UseCase;

use Magento\Framework\App\DeploymentConfig\FileReader;
use Magento\Framework\Config\File\ConfigFilePool;

class WaitAndNotWaitMessagesTest extends QueueTestCaseAbstract
{
    /**
     * @var FileReader
     */
    private $reader;

    /**
     * @var array
     */
    private $config;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->reader = $this->objectManager->get(FileReader::class);

        $this->config = $this->loadConfig();
    }

    public function testDefaultConfiguration()
    {
        $this->assertArraySubset(['queue' => ['consumers_wait_for_messages' => 1]], $this->config);
    }

    /**
     * @return array
     */
    private function loadConfig(): array
    {
        return $this->reader->load(ConfigFilePool::APP_ENV);
    }
}
