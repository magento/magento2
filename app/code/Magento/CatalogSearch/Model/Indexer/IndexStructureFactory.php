<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;

class IndexStructureFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $structures = null;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Configuration path by which current indexer handler stored
     *
     * @var string
     */
    private $configPath;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig
     * @param string $configPath
     * @param string[] $structures
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
