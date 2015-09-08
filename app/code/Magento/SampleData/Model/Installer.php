<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleData\Model;

/**
 * Model for installation Sample Data
 */
class Installer
{
    /**
     * @var \Magento\SampleData\Helper\Deploy
     */
    private $deploy;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $moduleList;

    /**
     * @var SetupFactory
     */
    private $setupFactory;

    /**
     * @var \Magento\SampleData\Helper\PostInstaller
     */
    private $postInstaller;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $session;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    private $userFactory;

    /**
     * @var \Magento\SampleData\Helper\State
     */
    private $state;

    /**
     * @var \Magento\SampleData\Model\Logger
     */
    private $logger;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\SampleData\Helper\Deploy $deploy
     * @param SetupFactory $setupFactory
     * @param \Magento\SampleData\Helper\PostInstaller $postInstaller
     * @param \Magento\Backend\Model\Auth\Session $session
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\SampleData\Helper\State $state
     * @param \Magento\SampleData\Model\Logger $logger
     */
    public function __construct(
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\SampleData\Helper\Deploy $deploy,
        \Magento\SampleData\Model\SetupFactory $setupFactory,
        \Magento\SampleData\Helper\PostInstaller $postInstaller,
        \Magento\Backend\Model\Auth\Session $session,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\SampleData\Helper\State $state,
        \Magento\SampleData\Model\Logger $logger
    ) {
        $this->deploy = $deploy;
        $this->moduleList = $moduleList;
        $this->setupFactory = $setupFactory;
        $this->postInstaller = $postInstaller;
        $this->session = $session;
        $this->userFactory = $userFactory;
        $this->state = $state;
        $this->logger = $logger;
    }

    /**
     * Run installation in context of the specified admin user
     *
     * @param $userName
     * @param array $modules
     * @return void
     * @throws \Exception
     */
    public function run($userName, array $modules = [])
    {
        set_time_limit(0);

        /** @var \Magento\User\Model\User $user */
        $user = $this->userFactory->create()->loadByUsername($userName);
        if (!$user->getId()) {
            throw new \Exception('Invalid admin user provided');
        }
        $this->state->start();
        $this->session->setUser($user);

        $this->deploy->run();

        $resources = $this->initResources($modules);
        $this->state->clearErrorFlag();
        try {
            foreach ($this->moduleList->getNames() as $moduleName) {
                if (isset($resources[$moduleName])) {
                    $resourceType = $resources[$moduleName];
                    $this->setupFactory->create($resourceType)->run();
                    $this->postInstaller->addModule($moduleName);
                }
            }
            $this->session->unsUser();
            $this->postInstaller->run();
            $this->state->finish();
        } catch (\Exception $e) {
            $this->state->setError();
            $this->logger->log($e->getMessage());
        }
    }

    /**
     * Init resources
     *
     * @param array $modules
     * @return array
     */
    private function initResources(array $modules)
    {
        $config = [];
        foreach (glob(__DIR__ . '/../config/*.php') as $filename) {
            if (is_file($filename)) {
                $configPart = include $filename;
                $config = array_merge_recursive($config, $configPart);
            }
        }

        if ($modules) {
            $config['setup_resources'] = array_intersect_key($config['setup_resources'], array_flip($modules));
        }

        return isset($config['setup_resources']) ? $config['setup_resources'] : [];
    }
}
