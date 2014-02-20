<?php
/**
 * Indexer application
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Index\App;

use Magento\App\Console\Response;
use Magento\LauncherInterface;

class Indexer implements LauncherInterface
{
    /**
     * Report directory
     *
     * @var string
     */
    protected $_reportDir;

    /**
     * @var \Magento\App\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Index\Model\IndexerFactory
     */
    protected $_indexerFactory;

    /**
     * @var \Magento\App\Console\Response
     */
    protected $_response;

    /**
     * @param string $reportDir
     * @param \Magento\App\Filesystem $filesystem
     * @param \Magento\Index\Model\IndexerFactory $indexerFactory
     * @param Response $response
     */
    public function __construct(
        $reportDir,
        \Magento\App\Filesystem $filesystem,
        \Magento\Index\Model\IndexerFactory $indexerFactory,
        Response $response
    ) {
        $this->_reportDir = $reportDir;
        $this->_filesystem = $filesystem;
        $this->_indexerFactory = $indexerFactory;
        $this->_response = $response;
    }

    /**
     * Run application
     *
     * @return \Magento\App\ResponseInterface
     */
    public function launch()
    {
        /* Clean reports */
        $directory = $this->_filesystem->getDirectoryWrite(\Magento\App\Filesystem::ROOT_DIR);
        $path = $directory->getRelativePath($this->_reportDir);
        if ($directory->isExist($path)) {
            $directory->delete($path);
        }

        /* Run all indexer processes */
        /** @var $indexer \Magento\Index\Model\Indexer */
        $indexer = $this->_indexerFactory->create();
        /** @var $process \Magento\Index\Model\Process */
        foreach ($indexer->getProcessesCollection() as $process) {
            if ($process->getIndexer()->isVisible()) {
                $process->reindexEverything();
            }
        }
        $this->_response->setCode(0);
        return $this->_response;
    }
}

