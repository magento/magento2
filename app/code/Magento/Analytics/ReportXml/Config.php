<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\ReportXml;

use Magento\Analytics\ReportXml\Config\Data;

/**
 * Class Config
 *
 * Config of ReportXml
 */
class Config implements ConfigInterface
{
    /**
     * @var Data
     */
    private $data;

    /**
     * Config constructor.
     *
     * @param Data $data
     */
    public function __construct(
        Data $data
    ) {
        $this->data = $data;
    }

    /**
     * Returns config value by name
     *
     * @param string $queryName
     * @return array
     */
    public function get($queryName)
    {
        return $this->data->get($queryName);
    }
}
