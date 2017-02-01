<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\App\Config\Source;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\DataObject;
use Magento\Config\Model\Placeholder\PlaceholderFactory;
use Magento\Config\Model\Placeholder\PlaceholderInterface;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class for retrieving configuration from environment variables.
 */
class EnvironmentConfigSource implements ConfigSourceInterface
{
    /**
     * Library for working with arrays.
     *
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * Object for working with placeholders for environment variables.
     *
     * @var PlaceholderInterface
     */
    private $placeholder;

    /**
     * @param ArrayManager $arrayManager
     * @param PlaceholderFactory $placeholderFactory
     */
    public function __construct(
        ArrayManager $arrayManager,
        PlaceholderFactory $placeholderFactory
    ) {
        $this->arrayManager = $arrayManager;
        $this->placeholder = $placeholderFactory->create(PlaceholderFactory::TYPE_ENVIRONMENT);
    }

    /**
     * @inheritdoc
     */
    public function get($path = '')
    {
        $data = new DataObject($this->loadConfig());
        return $data->getData($path) ?: [];
    }

    /**
     * Loads config from environment variables.
     *
     * @return array
     */
    private function loadConfig()
    {
        $config = [];

        $environmentVariables = $_ENV;

        foreach ($environmentVariables as $template => $value) {
            if (!$this->placeholder->isApplicable($template)) {
                continue;
            }

            $config = $this->arrayManager->set(
                $this->placeholder->restore($template),
                $config,
                $value
            );
        }

        return $config;
    }
}
