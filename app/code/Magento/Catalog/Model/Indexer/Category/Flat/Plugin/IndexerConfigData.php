<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Flat\Plugin;

class IndexerConfigData
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $state;

    /**
     * @param \Magento\Catalog\Model\Indexer\Category\Flat\State $state
     */
    public function __construct(\Magento\Catalog\Model\Indexer\Category\Flat\State $state)
    {
        $this->state = $state;
    }

    /**
     *  Unset indexer data in configuration if flat is disabled
     *
     * @param \Magento\Indexer\Model\Config\Data $subject
     * @param callable $proceed
     * @param string $path
     * @param mixed $default
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGet(
        \Magento\Indexer\Model\Config\Data $subject,
        \Closure $proceed,
        $path = null,
        $default = null
    ) {
        $data = $proceed($path, $default);

        if (!$this->state->isFlatEnabled()) {
            $indexerId = \Magento\Catalog\Model\Indexer\Category\Flat\State::INDEXER_ID;
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
