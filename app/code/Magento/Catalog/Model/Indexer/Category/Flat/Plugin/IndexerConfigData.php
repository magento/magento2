<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Flat\Plugin;

use Magento\Indexer\Model\Config\Data;
use Magento\Catalog\Model\Indexer\Category\Flat\State;

class IndexerConfigData
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @param State $state
     */
    public function __construct(State $state)
    {
        $this->state = $state;
    }

    /**
     *  Unset indexer data in configuration if flat is disabled
     *
     * @param Data $subject
     * @param mixed $data
     * @param string $path
     * @param mixed $default
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(Data $subject, $data, $path = null, $default = null)
    {
        if (!$this->state->isFlatEnabled()) {
            $indexerId = State::INDEXER_ID;
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
