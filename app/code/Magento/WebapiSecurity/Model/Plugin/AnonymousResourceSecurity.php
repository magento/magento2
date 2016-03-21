<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\WebapiSecurity\Model\Plugin;

use Magento\Webapi\Model\Config\Converter;

class AnonymousResourceSecurity
{
    const XML_ALLOW_INSECURE = 'system/webapisecurity/allow_insecure';

    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $backendConfig;

    /**
     * @var string
     */
    protected $resources;

    /**
     * AnonymousResourceSecurity constructor.
     *
     * @param \Magento\Backend\App\ConfigInterface $backendConfig
     * @param $resources
     */
    public function __construct(\Magento\Backend\App\ConfigInterface $backendConfig, $resources)
    {
        $this->backendConfig = $backendConfig;
        $this->resources = $resources;
    }

    public function afterConvert(
        Converter $subject,
        $nodes
    ) {
        if (empty($nodes)) {
            return $nodes;
        }
        $useInsecure = $this->backendConfig->isSetFlag(self::XML_ALLOW_INSECURE);
        if ($useInsecure) {
            foreach ($this->resources as $route => $requestType) {
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

    private function getNode($route, $requestType, $source)
    {
        if (isset($source[$route][$requestType])) {
            return $source[$route];
        }
        return null;
    }
}
