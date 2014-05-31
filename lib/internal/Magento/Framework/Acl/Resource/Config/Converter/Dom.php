<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Acl\Resource\Config\Converter;

class Dom implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $aclResourceConfig = array('config' => array('acl' => array('resources' => array())));
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
        $resourceData = array();
        $resourceAttributes = $resourceNode->attributes;
        $idNode = $resourceAttributes->getNamedItem('id');
        if (is_null($idNode)) {
            throw new \Exception('Attribute "id" is required for ACL resource.');
        }
        $resourceData['id'] = $idNode->nodeValue;
        $moduleNode = $resourceAttributes->getNamedItem('module');
        if (!is_null($moduleNode)) {
            $resourceData['module'] = $moduleNode->nodeValue;
        }
        $titleNode = $resourceAttributes->getNamedItem('title');
        if (!is_null($titleNode)) {
            $resourceData['title'] = $titleNode->nodeValue;
        }
        $sortOrderNode = $resourceAttributes->getNamedItem('sortOrder');
        $resourceData['sortOrder'] = !is_null($sortOrderNode) ? (int)$sortOrderNode->nodeValue : 0;
        $disabledNode = $resourceAttributes->getNamedItem('disabled');
        $resourceData['disabled'] = !is_null($disabledNode) && $disabledNode->nodeValue == 'true' ? true : false;
        // convert child resource nodes if needed
        $resourceData['children'] = array();
        /** @var $childNode \DOMNode */
        foreach ($resourceNode->childNodes as $childNode) {
            if ($childNode->nodeName == 'resource') {
                $resourceData['children'][] = $this->_convertResourceNode($childNode);
            }
        }
        return $resourceData;
    }
}
