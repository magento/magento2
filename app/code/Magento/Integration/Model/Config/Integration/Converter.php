<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\Config\Integration;

/**
 * Converter of api.xml content into array format.
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**#@+
     * Array keys for config internal representation.
     */
    const API_RESOURCES = 'resource';

    const API_RESOURCE_NAME = 'name';

    /**#@-*/

    /** @var \Magento\Framework\Acl\AclResource\ProviderInterface */
    protected $resourceProvider;

    /** @var \Magento\Integration\Helper\Data */
    protected $integrationData;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Acl\AclResource\ProviderInterface $resourceProvider
     * @param \Magento\Integration\Helper\Data $integrationData
     */
    public function __construct(
        \Magento\Framework\Acl\AclResource\ProviderInterface $resourceProvider,
        \Magento\Integration\Helper\Data $integrationData
    ) {
        $this->resourceProvider = $resourceProvider;
        $this->integrationData = $integrationData;
    }

    /**
     * {@inheritdoc}
     */
    public function convert($source)
    {
        $result = [];
        $allResources = $this->resourceProvider->getAclResources();
        $hashAclResourcesTree = $this->integrationData->hashResources($allResources[1]['children']);
        /** @var \DOMNodeList $integrations */
        $integrations = $source->getElementsByTagName('integration');
        /** @var \DOMElement $integration */
        foreach ($integrations as $integration) {
            if ($integration->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $integrationName = $integration->attributes->getNamedItem('name')->nodeValue;
            $result[$integrationName] = [];
            $result[$integrationName][self::API_RESOURCES] = [];
            /** @var \DOMNodeList $resources */
            $resources = $integration->getElementsByTagName('resource');
            /** @var \DOMElement $resource */
            foreach ($resources as $resource) {
                if ($resource->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $resource = $resource->attributes->getNamedItem('name')->nodeValue;
                $resourceNames = $this->integrationData->addParents($hashAclResourcesTree, $resource);
                foreach ($resourceNames as $name) {
                    $result[$integrationName][self::API_RESOURCES][] = $name;
                }
            }
            // Remove any duplicates added parents
            $result[$integrationName][self::API_RESOURCES] =
                array_unique($result[$integrationName][self::API_RESOURCES]);
        }
        return $result;
    }
}
