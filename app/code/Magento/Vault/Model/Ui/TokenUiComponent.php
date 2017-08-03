<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model\Ui;

/**
 * Class \Magento\Vault\Model\Ui\TokenUiComponent
 *
 * @since 2.1.0
 */
class TokenUiComponent implements TokenUiComponentInterface
{
    /**
     * @var array
     * @since 2.1.0
     */
    private $config;

    /**
     * @var string
     * @since 2.1.0
     */
    private $name;

    /**
     * @param array $config
     * @param string $name
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns component name
     *
     * @return string
     * @since 2.1.0
     */
    public function getName()
    {
        return $this->name;
    }
}
