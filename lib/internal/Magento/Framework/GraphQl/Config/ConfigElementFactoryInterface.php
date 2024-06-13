<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Config;

/**
 * Responsible for translating data produced by configuration readers to config objects
 *
 * @api
 */
interface ConfigElementFactoryInterface
{
    /**
     * Map data from passed by config readers to a data object format
     *
     * @param array $data
     * @return ConfigElementInterface
     */
    public function createFromConfigData(array $data) : ConfigElementInterface;
}
