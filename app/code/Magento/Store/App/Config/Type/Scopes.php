<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\App\Config\Type;

use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\DataObject;

/**
 * Merge and hold scopes data from different sources
 *
 * @package Magento\Store\App\Config\Type
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
     * System constructor.
     * @param ConfigSourceInterface $source
     */
    public function __construct(
        ConfigSourceInterface $source
    ) {
        $this->source = $source;
        $this->data = new DataObject();
    }

    /**
     * @inheritdoc
     */
    public function get($path = '')
    {
        $patchChunks = explode("/", $path);

        if (!$this->data->getData($path) || count($patchChunks) == 1) {
            $this->data->addData($this->source->get($path));
        }

        return $this->data->getData($path);
    }

    /**
     * Clean cache
     *
     * @return void
     */
    public function clean()
    {
        $this->data = new DataObject();
    }
}
