<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\AclResource\Config\Converter;

/**
 * @inheritDoc
 */
class Dom implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * @inheritdoc
     *
     * @param \DOMDocument $source
     * @return array
     * @throws \Exception
     */
    public function convert($source)
    {
        $aclResourceConfig = ['config' => ['acl' => ['resources' => []]]];
        $xpath = new \DOMXPath($source);
        /** @var $resourceNode \DOMNode */
        foreach ($xpath->query('/config/acl/resources/resource') as $resourceNode) {
            $aclResourceConfig['config']['acl']['resources'][] = $this->_convertResourceNode($resourceNode);
        }
        return $aclResourceConfig;
    }

    /**
     * Convert resource node into assoc array
     *
     * @param \DOMNode $resourceNode
     * @return array
     * @throws \Exception
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _convertResourceNode(\DOMNode $resourceNode)
    {
        $resourceData = [];
        $resourceAttributes = $resourceNode->attributes;
        $idNode = $resourceAttributes->getNamedItem('id');
        if ($idNode === null) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception('Attribute "id" is required for ACL resource.');
        }
        $resourceData['id'] = $idNode->nodeValue;
        $moduleNode = $resourceAttributes->getNamedItem('module');
        if ($moduleNode !== null) {
            $resourceData['module'] = $moduleNode->nodeValue;
        }
        $titleNode = $resourceAttributes->getNamedItem('title');
        if ($titleNode !== null) {
            $resourceData['title'] = $titleNode->nodeValue;
        }
        $sortOrderNode = $resourceAttributes->getNamedItem('sortOrder');
        $resourceData['sortOrder'] = $sortOrderNode !== null ? (int)$sortOrderNode->nodeValue : 0;
        $disabledNode = $resourceAttributes->getNamedItem('disabled');
        $resourceData['disabled'] = $disabledNode !== null && $disabledNode->nodeValue == 'true';
        // convert child resource nodes if needed
        $resourceData['children'] = [];
        /** @var $childNode \DOMNode */
        foreach ($resourceNode->childNodes as $childNode) {
            if ($childNode->nodeName == 'resource') {
                $resourceData['children'][] = $this->_convertResourceNode($childNode);
            }
        }
        return $resourceData;
    }
}
