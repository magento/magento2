<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryMultiDimensionalIndexerApi\Model;

use Magento\Framework\ObjectManagerInterface;

/**
 * Index Name builder. It is Facade for simplifying IndexName object creation
 *
 * @api
 */
class IndexNameBuilder
{
    /**
     * Index id parameter name. Used internally in this object
     *
     * Can not replace on private constant (feature of PHP 7.1) because we need to support PHP 7.0
     */
    private static $indexId = 'indexId';

    /**
     * Dimensions parameter name. Used internally in this object
     *
     * Can not replace on private constant (feature of PHP 7.1) because we need to support PHP 7.0
     */
    private static $dimensions = 'dimensions';

    /**
     * Alias parameter name. Used internally in this object
     *
     * Can not replace on private constant (feature of PHP 7.1) because we need to support PHP 7.0
     */
    private static $alias = 'alias';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @var AliasFactory
     */
    private $aliasFactory;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param DimensionFactory $dimensionFactory
     * @param AliasFactory $aliasFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        DimensionFactory $dimensionFactory,
        AliasFactory $aliasFactory
    ) {
        $this->objectManager = $objectManager;
        $this->dimensionFactory = $dimensionFactory;
        $this->aliasFactory = $aliasFactory;
    }

    /**
     * @param string $indexId
     * @return self
     */
    public function setIndexId(string $indexId): self
    {
        $this->data[self::$indexId] = $indexId;
        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     * @return self
     */
    public function addDimension(string $name, string $value): self
    {
        $this->data[self::$dimensions][] = $this->dimensionFactory->create([
            'name' => $name,
            'value' => $value,
        ]);
        return $this;
    }

    /**
     * @param string $alias
     * @return self
     */
    public function setAlias(string $alias): self
    {
        $this->data[self::$alias] = $this->aliasFactory->create(['value' => $alias]);
        return $this;
    }

    /**
     * @return IndexName
     */
    public function build(): IndexName
    {
        $indexName = $this->objectManager->create(IndexName::class, $this->data);
        $this->data = [];
        return $indexName;
    }
}
