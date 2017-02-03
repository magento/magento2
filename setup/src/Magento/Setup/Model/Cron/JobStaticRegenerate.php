<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Magento\Framework\App\Cache;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\DeploymentConfig\Writer;

/**
 * Static regenerate job
 */
class JobStaticRegenerate extends AbstractJob
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\Cache
     */
    protected $cache;

    /**
     * @var \Magento\Framework\App\State\CleanupFiles
     */
    protected $cleanupFiles;

    /**
     * @var \Magento\Setup\Model\Cron\Status
     */
    protected $status;

    /**
     *  Constructor
     *
     * @param \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Magento\Setup\Model\Cron\Status $status
     * @param array $name
     * @param array $params
     */
    public function __construct(
        \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider,
        \Symfony\Component\Console\Output\OutputInterface $output,
        \Magento\Setup\Model\Cron\Status $status,
        $name,
        $params = []
    ) {
        $this->objectManager = $objectManagerProvider->get();
        $this->cleanupFiles = $this->objectManager->get('Magento\Framework\App\State\CleanupFiles');
        $this->cache = $this->objectManager->get('Magento\Framework\App\Cache');
        $this->output = $output;
        $this->status = $status;

        parent::__construct($output, $status, $objectManagerProvider, $name, $params);
    }

    /**
     * Execute job
     *
     * @throws \RuntimeException
     * @return void
     */
    public function execute()
    {
        try {
            $mode = $this->getModeObject();
            if ($mode->getMode() == State::MODE_PRODUCTION) {
                $filesystem = $this->getFilesystem();
                $filesystem->regenerateStatic($this->getOutputObject());
            } else {
                $this->getStatusObject()->add('Cleaning generated files...');
                $this->getCleanFilesObject()->clearCodeGeneratedFiles();
                $this->getStatusObject()->add('Clearing cache...');
                $this->getCacheObject()->clean();
                $this->getStatusObject()->add('Cleaning static view files');
                $this->getCleanFilesObject()->clearMaterializedViewFiles();
            }
        } catch (\Exception $e) {
            $this->getStatusObject()->toggleUpdateError(true);
            throw new \RuntimeException(sprintf('Could not complete %s successfully: %s', $this, $e->getMessage()));
        }
    }

    /**
     * Returns cache object
     *
     * @return \Magento\Framework\App\Cache
     */
    public function getCacheObject()
    {
        return $this->cache;
    }

    /**
     * Returns CleanFiles object
     *
     * @return \Magento\Framework\App\State\CleanupFiles
     */
    public function getCleanFilesObject()
    {
        return $this->cleanupFiles;
    }

    /**
     * Returns Status object
     *
     * @return \Magento\Setup\Model\Cron\Status
     */
    public function getStatusObject()
    {
        return $this->status;
    }

    /**
     * Returns output object
     *
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    public function getOutputObject()
    {
        return $this->output;
    }

    /**
     * Returns filesystem object
     *
     * @return \Magento\Deploy\Model\Filesystem
     */
    public function getFilesystem()
    {
        return $this->objectManager->create('Magento\Deploy\Model\Filesystem');
    }

    /**
     * Returns mode object
     *
     * @return \Magento\Deploy\Model\Mode
     */
    public function getModeObject()
    {
        return $this->objectManager->create(
            'Magento\Deploy\Model\Mode',
            [
                'input' => new ArrayInput([]),
                'output' => $this->output,
            ]
        );
    }
}
