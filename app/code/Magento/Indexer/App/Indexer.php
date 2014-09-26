<?php
/**
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
namespace Magento\Indexer\App;

use Magento\Framework\App;

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
     * @param string $reportDir
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Indexer\Model\Processor $processor
     */
    public function __construct(
        $reportDir,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Indexer\Model\Processor $processor
    ) {
        $this->reportDir = $reportDir;
        $this->filesystem = $filesystem;
        $this->processor = $processor;
    }

    /**
     * Run application
     *
     * @return int
     */
    public function launch()
    {
        /* Clean reports */
        $directory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem::ROOT_DIR);
        $path = $directory->getRelativePath($this->reportDir);
        if ($directory->isExist($path)) {
            $directory->delete($path);
        }

        /* Regenerate all indexers */
        $this->processor->reindexAll();

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function catchException(App\Bootstrap $bootstrap, \Exception $exception)
    {
        return false;
    }
}
