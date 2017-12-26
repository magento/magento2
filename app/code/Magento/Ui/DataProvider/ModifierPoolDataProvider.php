<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider;

use Magento\Framework\App\ObjectManager;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\Pool;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class ModifierPoolDataProvider extends AbstractDataProvider
{
    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $pool
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->pool = $pool ?: ObjectManager::getInstance()->get(PoolInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        $this->getConfigData();

        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $this->data = $modifier->modifyData($this->data);
        }

        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function getMeta()
    {
        $this->meta = parent::getMeta();

        /** @var ModifierInterface $modifier */
        $this->pool->getModifiers();

        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $this->meta = $modifier->modifyMeta($this->meta);
        }

        return $this->meta;
    }
}
