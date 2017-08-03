<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Braintree\Gateway\Config\Config;

/**
 * Class DescriptorDataBuilder
 * @since 2.2.0
 */
class DescriptorDataBuilder implements BuilderInterface
{
    /**
     * @var string
     * @since 2.2.0
     */
    private static $descriptorKey = 'descriptor';

    /**
     * @var Config
     * @since 2.2.0
     */
    private $config;

    /**
     * DescriptorDataBuilder constructor.
     * @param Config $config
     * @since 2.2.0
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function build(array $buildSubject)
    {
        $values = $this->config->getDynamicDescriptors();
        return !empty($values) ? [self::$descriptorKey => $values] : [];
    }
}
