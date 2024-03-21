<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsyncConfig\Model;

use Magento\AsyncConfig\Api\Data\AsyncConfigMessageInterface;
use Magento\Config\Controller\Adminhtml\System\Config\Save;
use Magento\Config\Model\Config\Factory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Symfony\Component\Console\Output\ConsoleOutput;

class Consumer
{
    /**
     * Backend Config Model Factory
     *
     * @var Factory
     */
    private $configFactory;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var ScopeInterface
     */
    private $scope;

    /**
     * @var Save
     */
    private $save;

    /**
     * @var ConsoleOutput
     */
    private $output;

    /**
     * @param Factory $configFactory
     * @param Json $json
     * @param ScopeInterface $scope
     * @param ConsoleOutput $output
     */
    public function __construct(
        Factory $configFactory,
        Json $json,
        ScopeInterface $scope,
        ConsoleOutput $output
    ) {
        $this->configFactory = $configFactory;
        $this->serializer = $json;
        $this->scope = $scope;
        $this->output = $output;
        $this->scope->setCurrentScope('adminhtml');
        $this->save = ObjectManager::getInstance()->get(Save::class);
        $this->scope->setCurrentScope('global');
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
        $data = $this->save->filterNodes($data);
        /** @var \Magento\Config\Model\Config $configModel */
        $configModel = $this->configFactory->create(['data' => $data]);
        try {
            $configModel->save();
        } catch (LocalizedException $exception) {
            $message = $exception->getMessage();
            $this->output->writeln(' Config couldn\'t be saved: ' . $message);
        }
    }
}
