<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory class to create jobs
 */
class JobFactory
{
    /**
     * Name of jobs
     */
    const NAME_UPGRADE = 'setup:upgrade';
    const DB_ROLLBACK = 'setup:rollback';

    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * Constructor
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Create job instance.
     *
     * @param string $name
     * @param array $params
     * @return AbstractJob
     * @throws \RuntimeException
     */
    public function create($name, array $params = [])
    {
        $cronStatus = $this->serviceLocator->get('Magento\Setup\Model\Cron\Status');
        $statusStream = fopen($cronStatus->getStatusFilePath(), 'a+');
        $logStream = fopen($cronStatus->getLogFilePath(), 'a+');
        $multipleStreamOutput = new MultipleStreamOutput([$statusStream, $logStream]);
        $objectManagerProvider = $this->serviceLocator->get('Magento\Setup\Model\ObjectManagerProvider');
        /** @var \Magento\Framework\ObjectManagerInterface $objectManager */
        $objectManager = $objectManagerProvider->get();
        switch ($name) {
            case self::NAME_UPGRADE:
                return new JobUpgrade(
                    $this->serviceLocator->get('Magento\Setup\Console\Command\UpgradeCommand'),
                    $objectManagerProvider,
                    $multipleStreamOutput,
                    $cronStatus,
                    $name,
                    $params
                );
                break;
            case self::DB_ROLLBACK:

                return $objectManager->create(
                    '\Magento\Setup\Model\Cron\JobDbRollback',
                    [
                        'output' => $multipleStreamOutput,
                        'cronStatus' => $cronStatus,
                        'name' => $name,
                        'params' => $params
                    ]
                );
                break;
            default:
                throw new \RuntimeException(sprintf('"%s" job is not supported.', $name));
        }
    }
}
