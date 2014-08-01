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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Module;

use Magento\Module\Setup\Connection\AdapterInterface;
use Magento\Setup\Model\Logger;

class SetupFactory
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var Setup\FileResolver
     */
    protected $fileResolver;

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param AdapterInterface $connection
     * @param ModuleListInterface $moduleList
     * @param Setup\FileResolver $setupFileResolver
     * @param Logger $logger
     */
    public function __construct(
        AdapterInterface $connection,
        ModuleListInterface $moduleList,
        Setup\FileResolver $setupFileResolver,
        Logger $logger
    ) {
        $this->logger = $logger;
        $this->adapter = $connection;
        $this->moduleList = $moduleList;
        $this->fileResolver = $setupFileResolver;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->configuration = $config;
    }

    /**
     * @param string $moduleName
     * @return Setup
     */
    public function create($moduleName)
    {
        $setup =  new Setup(
            $this->adapter,
            $this->moduleList,
            $this->fileResolver,
            $this->logger,
            $moduleName,
            $this->configuration
        );
        $setup->setTablePrefix($this->configuration['db_prefix']);

        return $setup;
    }
}
