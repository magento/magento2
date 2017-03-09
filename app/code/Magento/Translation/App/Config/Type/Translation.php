<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\App\Config\Type;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\DataObject;

/**
 * Class which hold all translation sources and merge them
 *
 * @package Magento\Translation\App\Config\Type
 */
class Translation implements ConfigTypeInterface
{
    const CONFIG_TYPE = "i18n";

    /**
     * @var DataObject[]
     */
    private $data;

    /**
     * @var ConfigSourceInterface
     */
    private $source;

    /**
     * Translation constructor.
     * @param ConfigSourceInterface $source
     */
    public function __construct(
        ConfigSourceInterface $source
    ) {
        $this->source = $source;
    }

    /**
     * @inheritDoc
     */
    public function get($path = '')
    {
        if (!$this->data) {
            $this->data = new DataObject($this->source->get());
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
        $this->data = null;
    }
}
