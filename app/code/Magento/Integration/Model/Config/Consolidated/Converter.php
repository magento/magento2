<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\Config\Consolidated;

/**
 * Converter of integration.xml content into array format.
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**#@+
     * Array keys for config internal representation.
     */
    const KEY_EMAIL = 'email';
    const KEY_AUTHENTICATION_ENDPOINT_URL = 'endpoint_url';
    const KEY_IDENTITY_LINKING_URL = 'identity_link_url';
    const API_RESOURCES = 'resource';
    const API_RESOURCE_NAME = 'name';

    /**#@-*/

    /** @var \Magento\Framework\Acl\AclResource\ProviderInterface */
    protected $resourceProvider;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Acl\AclResource\ProviderInterface $resourceProvider
     */
    public function __construct(
        \Magento\Framework\Acl\AclResource\ProviderInterface $resourceProvider
    ) {
        $this->resourceProvider = $resourceProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function convert($source)
    {
        $result = [];
        $allResources = $this->resourceProvider->getAclResources();
        $hashAclResourcesTree = $this->hashResources($allResources[1]['children']);
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

            /** @var \DOMElement $email */
            $email = $integration->getElementsByTagName('email')->item(0)->nodeValue;
            /** @var \DOMNodeList $resources */
            $resources = $integration->getElementsByTagName('resource');
            $result[$integrationName][self::KEY_EMAIL] = $email;
            if ($integration->getElementsByTagName('endpoint_url')->length) {
                /** @var \DOMElement $endpointUrl */
                $endpointUrl = $integration->getElementsByTagName('endpoint_url')->item(0)->nodeValue;
                $result[$integrationName][self::KEY_AUTHENTICATION_ENDPOINT_URL] = $endpointUrl;
            }
            if ($integration->getElementsByTagName('identity_link_url')->length) {
                /** @var \DOMElement $identityLinkUrl */
                $identityLinkUrl = $integration->getElementsByTagName('identity_link_url')->item(0)->nodeValue;
                $result[$integrationName][self::KEY_IDENTITY_LINKING_URL] = $identityLinkUrl;
            }
            /** @var \DOMElement $resource */
            foreach ($resources as $resource) {
                if ($resource->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $resource = $resource->attributes->getNamedItem('name')->nodeValue;
                $resourceNames = $this->addParentsToResource($hashAclResourcesTree, $resource);
                foreach ($resourceNames as $name) {
                    $result[$integrationName][self::API_RESOURCES][] = $name;
                }
            }
            // Remove any duplicates added parents
            $result[$integrationName][self::API_RESOURCES] =
                array_values(array_unique($result[$integrationName][self::API_RESOURCES]));
        }
        return $result;
    }

    /**
     * Make ACL resource array return a hash with parent-resource-name => [children-resources-names] representation
     *
     * @param array $resources
     * @return array
     */
    private function hashResources(array $resources)
    {
        $output = [];
        foreach ($resources as $resource) {
            if (isset($resource['children'])) {
                $item = $this->hashResources($resource['children']);
            } else {
                $item = [];
            }
            $output[$resource['id']] = $item;
        }
        return $output;
    }

    /**
     * Find parents names of a node in an ACL resource hash and add them to returned array
     *
     * @param array $resourcesHash
     * @param string $nodeName
     * @return array
     */
    private function addParentsToResource(array $resourcesHash, $nodeName)
    {
        $output = [];
        foreach ($resourcesHash as $resource => $children) {
            if ($resource == $nodeName) {
                $output = [$resource];
                break;
            }
            if (!empty($children)) {
                $names = $this->addParentsToResource($children, $nodeName);
                if (!empty($names)) {
                    $output = array_merge([$resource], $names);
                    break;
                } else {
                    continue;
                }
            }
        }
        return $output;
    }
}
