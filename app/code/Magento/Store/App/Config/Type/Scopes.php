<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\App\Config\Type;

use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;

/**
 * Merge and hold scopes data from different sources
 */
class Scopes implements ConfigTypeInterface
{
    const CONFIG_TYPE = 'scopes';

    /**
     * @var ConfigSourceInterface
     */
    private $source;

    /**
     * @var DataObject[]
     */
    private $data;

    /**
     * Map between scope id and scope code
     *
     * @var array
     */
    private $idCodeMap = [];

    /**
     * The field names holder of scope id for specific scope pool.
     * Used for map id to code, e.g. websites/0 to websites/admin
     *
     * @var array
     */
    private $scopeIdField = [
        ScopeInterface::SCOPE_WEBSITES => 'website_id',
        ScopeInterface::SCOPE_STORES => 'store_id',
    ];

    /**
     * @param ConfigSourceInterface $source
     */
    public function __construct(
        ConfigSourceInterface $source
    ) {
        $this->source = $source;
    }

    /**
     * @inheritdoc
     */
    public function get($path = '')
    {
        if (null === $this->data) {
            $this->data = new DataObject($this->source->get());
        }

        $patchChunks = explode('/', $path);

        if (isset($patchChunks[1])
            && is_numeric($patchChunks[1])
            && in_array($patchChunks[0], [ScopeInterface::SCOPE_WEBSITES, ScopeInterface::SCOPE_STORES], true)
        ) {
            $path = $this->convertIdPathToCodePath($patchChunks);
        }

        return $this->data->getData($path);
    }

    /**
     * Replace scope id with scope code.
     * E.g. path 'websites/admin' will be converted to 'websites/0'
     *
     * @param array $patchChunks
     * @return string
     */
    private function convertIdPathToCodePath(array $patchChunks)
    {
        list($scopePool, $scopeId) = $patchChunks;
        if (!isset($this->idCodeMap[$scopePool]) || !array_key_exists($scopeId, $this->idCodeMap[$scopePool])) {
            $scopeData = $this->data->getData($scopePool);
            foreach ((array)$scopeData as $scopeEntity) {
                if (!isset($scopeEntity[$this->scopeIdField[$scopePool]])) {
                    continue;
                }
                $this->idCodeMap[$scopePool][$scopeEntity[$this->scopeIdField[$scopePool]]] = $scopeEntity['code'];
            }

            if (!isset($this->idCodeMap[$scopePool][$scopeId])) {
                $this->idCodeMap[$scopePool][$scopeId] = null;
            }
        }

        if ($this->idCodeMap[$scopePool][$scopeId]) {
            $patchChunks[1] = $this->idCodeMap[$scopePool][$scopeId];
        }

        return implode('/', $patchChunks);
    }

    /**
     * Clean cache
     *
     * @return void
     */
    public function clean()
    {
        $this->data = null;
    }
}
