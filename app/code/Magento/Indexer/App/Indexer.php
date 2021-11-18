<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\App;

use Magento\Framework\App;
use Magento\Framework\App\Filesystem\DirectoryList;

class Indexer implements \Magento\Framework\AppInterface
{
    /**
     * Report directory
     *
     * @var string
     */
    protected $reportDir;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\App\Console\Response
     */
    protected $_response;

    /**
     * @var \Magento\Indexer\Model\Processor
     */
    private $processor;

    /**
     * @param string $reportDir
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Indexer\Model\Processor $processor
     * @param \Magento\Framework\App\Console\Response $response
     */
    public function __construct(
        $reportDir,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Indexer\Model\Processor $processor,
        \Magento\Framework\App\Console\Response $response
    ) {
        $this->reportDir = $reportDir;
        $this->filesystem = $filesystem;
        $this->processor = $processor;
        $this->_response = $response;
    }

    /**
     * Run application
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function launch()
    {
        /* Clean reports */
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $path = $directory->getRelativePath($this->reportDir);
        if ($directory->isExist($path)) {
            $directory->delete($path);
        }

        /* Regenerate all indexers */
        $this->processor->reindexAll();
        $this->_response->setCode(0);

        return $this->_response;
    }

    /**
     * {@inheritdoc}
     */
    public function catchException(App\Bootstrap $bootstrap, \Exception $exception)
    {
        return false;
    }
}
