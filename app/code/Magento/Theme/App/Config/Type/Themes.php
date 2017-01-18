<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\App\Config\Type;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\DataObject;

/**
 * Class Themes.
 *
 * Retrieves data from theme source.
 */
class Themes implements ConfigTypeInterface
{
    /**
     * The type of config.
     */
    const CONFIG_TYPE = 'themes';

    /**
     * A config source.
     *
     * @var ConfigSourceInterface
     */
    private $configSource;

    /**
     * A themes data.
     *
     * @var DataObject
     */
    private $data;

    /**
     * @param ConfigSourceInterface $configSource A config source
     */
    public function __construct(ConfigSourceInterface $configSource)
    {
        $this->configSource = $configSource;
    }

    /**
     * Retrieves themes configuration by path.
     *
     * {@inheritdoc}
     */
    public function get($path = '')
    {
        if (!$this->data) {
            $this->data = new DataObject($this->configSource->get());
        }

        return $this->data->getData($path);
    }

    /**
     * Cleans persisted themes data.
     *
     * {@inheritdoc}
     */
    public function clean()
    {
        $this->data = null;
    }
}
