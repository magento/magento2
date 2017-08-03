<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\App\Config\Type;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\DataObject;

/**
 * Class which hold all translation sources and merge them
 * @since 2.1.3
 */
class Translation implements ConfigTypeInterface
{
    const CONFIG_TYPE = "i18n";

    /**
     * @var DataObject[]
     * @since 2.1.3
     */
    private $data;

    /**
     * @var ConfigSourceInterface
     * @since 2.1.3
     */
    private $source;

    /**
     * Translation constructor.
     * @param ConfigSourceInterface $source
     * @since 2.1.3
     */
    public function __construct(
        ConfigSourceInterface $source
    ) {
        $this->source = $source;
    }

    /**
     * @inheritDoc
     * @since 2.1.3
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
     * @since 2.1.3
     */
    public function clean()
    {
        $this->data = null;
    }
}
