<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\ReportXml;

use Magento\Framework\Config\DataInterface;

/**
 * Class Config
 *
 * Config of ReportXml
 * @since 2.2.0
 */
class Config implements ConfigInterface
{
    /**
     * @var DataInterface
     * @since 2.2.0
     */
    private $data;

    /**
     * Config constructor.
     *
     * @param DataInterface $data
     * @since 2.2.0
     */
    public function __construct(
        DataInterface $data
    ) {
        $this->data = $data;
    }

    /**
     * Returns config value by name
     *
     * @param string $queryName
     * @return array
     * @since 2.2.0
     */
    public function get($queryName)
    {
        return $this->data->get($queryName);
    }
}
