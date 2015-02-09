<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Plugin;

class IndexerConfigData
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\State
     */
    protected $_state;

    /**
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State $state
     */
    public function __construct(\Magento\Catalog\Model\Indexer\Product\Flat\State $state)
    {
        $this->_state = $state;
    }

    /**
     * Around get handler
     *
     * @param \Magento\Indexer\Model\Config\Data $subject
     * @param callable $proceed
     * @param string $path
     * @param string $default
     *
     * @return mixed|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     */
    public function aroundGet(
        \Magento\Indexer\Model\Config\Data $subject,
        \Closure $proceed,
        $path = null,
        $default = null
    ) {
        $data = $proceed($path, $default);

        if (!$this->_state->isFlatEnabled()) {
            $indexerId = \Magento\Catalog\Model\Indexer\Product\Flat\Processor::INDEXER_ID;
            if (!$path && isset($data[$indexerId])) {
                unset($data[$indexerId]);
            } elseif ($path) {
                list($firstKey,) = explode('/', $path);
                if ($firstKey == $indexerId) {
                    $data = $default;
                }
            }
        }

        return $data;
    }
}
