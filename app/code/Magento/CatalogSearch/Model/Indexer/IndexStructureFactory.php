<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @api
 * @since 2.1.0
 */
class IndexStructureFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     * @since 2.1.0
     */
    protected $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     * @since 2.1.0
     */
    protected $structures = null;

    /**
     * @var ScopeConfigInterface
     * @since 2.1.0
     */
    private $scopeConfig;

    /**
     * Configuration path by which current indexer handler stored
     *
     * @var string
     * @since 2.1.0
     */
    private $configPath;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig
     * @param string $configPath
     * @param string[] $structures
     * @since 2.1.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig,
        $configPath,
        array $structures = []
    ) {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->configPath = $configPath;
        $this->structures = $structures;
    }

    /**
     * Create index structure
     *
     * @param array $data
     * @return IndexStructureInterface
     * @since 2.1.0
     */
    public function create(array $data = [])
    {
        $currentStructure = $this->scopeConfig->getValue($this->configPath, ScopeInterface::SCOPE_STORE);
        if (!isset($this->structures[$currentStructure])) {
            throw new \LogicException(
                'There is no such index structure: ' . $currentStructure
            );
        }
        $indexStructure = $this->objectManager->create($this->structures[$currentStructure], $data);

        if (!$indexStructure instanceof IndexStructureInterface) {
            throw new \InvalidArgumentException(
                $indexStructure . ' doesn\'t implement \Magento\Framework\Indexer\IndexStructureInterface'
            );
        }

        return $indexStructure;
    }
}
