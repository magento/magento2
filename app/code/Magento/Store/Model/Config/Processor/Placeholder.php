<?php
/**
 * Placeholder configuration values processor. Replace placeholders in configuration with config values
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Processor;

use Magento\Framework\App\Config\Spi\PostProcessorInterface;
use Magento\Store\Model\Config\Placeholder as ConfigPlaceholder;
use Magento\Framework\App\ObjectManager;

/**
 * Placeholder configuration values processor. Replace placeholders in configuration with config values
 * @package Magento\Store\Model\Config\Processor
 */
class Placeholder implements PostProcessorInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     *
     * @deprecated
     */
    protected $request;

    /**
     * @var string[]
     *
     * @deprecated
     */
    protected $urlPaths;

    /**
     * @var string
     *
     * @deprecated
     */
    protected $urlPlaceholder;

    /**
     * @var ConfigPlaceholder
     */
    private $configPlaceholder;

    /**
     * Placeholder constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string[] $urlPaths
     * @param string $urlPlaceholder
     * @param ConfigPlaceholder|null $configPlaceholder
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        $urlPaths,
        $urlPlaceholder,
        ConfigPlaceholder $configPlaceholder = null
    ) {
        $this->request = $request;
        $this->urlPaths = $urlPaths;
        $this->urlPlaceholder = $urlPlaceholder;
        $this->configPlaceholder = $configPlaceholder ?: ObjectManager::getInstance()->get(ConfigPlaceholder::class);
    }

    /**
     * @inheritdoc
     */
    public function process(array $data)
    {
        foreach ($data as $scope => &$scopeData) {
            if ($scope === 'default') {
                $scopeData = $this->configPlaceholder->process($scopeData);
            } else {
                foreach ($scopeData as &$sData) {
                    $sData = $this->configPlaceholder->process($sData);
                }
            }
        }

        return $data;
    }
}
