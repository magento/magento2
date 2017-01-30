<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\Ui;

class TokenUiComponent implements TokenUiComponentInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $name;

    /**
     * @param array $config
     * @param string $name
     */
    public function __construct(
        array $config,
        $name
    ) {
        $this->config = $config;
        $this->name = $name;
    }

    /**
     * Returns component configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns component name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
