<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\WebapiSecurity\Model\Plugin;

use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Webapi\Model\Config\Converter;

class AnonymousResourceSecurity
{
    /**
     * Config path
     */
    const XML_ALLOW_INSECURE = 'webapi/webapisecurity/allow_insecure';

    /**
     * AnonymousResourceSecurity constructor.
     *
     * @param ReinitableConfigInterface $config
     * @param array $resources
     */
    public function __construct(
        protected readonly ReinitableConfigInterface $config,
        protected $resources
    ) {
    }

    /**
     * Filter config values.
     *
     * @param Converter $subject
     * @param array $nodes
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterConvert(Converter $subject, $nodes)
    {
        if (empty($nodes)) {
            return $nodes;
        }
        $useInsecure = $this->config->getValue(self::XML_ALLOW_INSECURE);
        if ($useInsecure) {
            foreach (array_keys($this->resources) as $resource) {
                list($route, $requestType) = explode("::", $resource);
                if ($result = $this->getNode($route, $requestType, $nodes["routes"])) {
                    if (isset($result[$requestType]['resources'])) {
                        $result[$requestType]['resources'] = ['anonymous' => true];
                        $nodes['routes'][$route] = $result;
                    }

                    if (isset($result[$requestType]['service']['class'])
                        && isset($result[$requestType]['service']['method'])
                    ) {
                        $serviceName = $result[$requestType]['service']['class'];
                        $serviceMethod = $result[$requestType]['service']['method'];
                        $nodes['services'][$serviceName]['V1']['methods'][$serviceMethod]['resources'] = ['anonymous'];
                    }
                }
            }
        }

        return $nodes;
    }

    /**
     * Get node by path.
     *
     * @param string $route
     * @param string $requestType
     * @param array $source
     * @return array|null
     */
    private function getNode($route, $requestType, $source)
    {
        if (isset($source[$route][$requestType])) {
            return $source[$route];
        }
        return null;
    }
}
