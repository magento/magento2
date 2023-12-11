<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer;

use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\EngineResolverInterface;

/**
 * Index structure factory
 *
 * @api
 * @since 100.1.0
 */
class IndexStructureFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     * @since 100.1.0
     */
    protected $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     * @since 100.1.0
     */
    protected $structures = null;
    /**
     * @var EngineResolverInterface
     */
    private $engineResolver;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param EngineResolverInterface $engineResolver
     * @param string[] $structures
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        EngineResolverInterface $engineResolver,
        array $structures = []
    ) {
        $this->objectManager = $objectManager;
        $this->structures = $structures;
        $this->engineResolver = $engineResolver;
    }

    /**
     * Create index structure
     *
     * @param array $data
     * @return IndexStructureInterface
     * @since 100.1.0
     */
    public function create(array $data = [])
    {
        $currentStructure = $this->engineResolver->getCurrentSearchEngine();
        if (!isset($this->structures[$currentStructure])) {
            throw new \LogicException(
                'There is no such index structure: ' . $currentStructure
            );
        }
        $indexStructure = $this->objectManager->create($this->structures[$currentStructure], $data);

        if (!$indexStructure instanceof IndexStructureInterface) {
            throw new \InvalidArgumentException(
                $currentStructure . ' index structure doesn\'t implement '. IndexStructureInterface::class
            );
        }

        return $indexStructure;
    }
}
