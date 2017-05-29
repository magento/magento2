<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Processor;

use Magento\Config\Model\Placeholder\PlaceholderFactory;
use Magento\Config\Model\Placeholder\PlaceholderInterface;
use Magento\Framework\App\Config\Spi\PreProcessorInterface;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Allows to extract configurations from environment variables.
 */
class EnvironmentPlaceholder implements PreProcessorInterface
{
    /**
     * @var PlaceholderFactory
     */
    private $placeholderFactory;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var PlaceholderInterface
     */
    private $placeholder;

    /**
     * @param PlaceholderFactory $placeholderFactory
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        PlaceholderFactory $placeholderFactory,
        ArrayManager $arrayManager
    ) {
        $this->placeholderFactory = $placeholderFactory;
        $this->arrayManager = $arrayManager;
        $this->placeholder = $placeholderFactory->create(PlaceholderFactory::TYPE_ENVIRONMENT);
    }

    /**
     * Method extracts environment variables.
     * If environment variable is matching the desired rule - it's being used as value.
     *
     * {@inheritdoc}
     */
    public function process(array $config)
    {
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
