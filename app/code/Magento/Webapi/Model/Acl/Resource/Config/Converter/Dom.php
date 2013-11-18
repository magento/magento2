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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Model\Acl\Resource\Config\Converter;

class Dom extends \Magento\Acl\Resource\Config\Converter\Dom
{
    /**
     * {@inheritdoc}
     */
    public function convert($source)
    {
        $aclResourceConfig = parent::convert($source);
        $aclResourceConfig['config']['mapping'] = array();
        $xpath = new \DOMXPath($source);
        /** @var $mappingNode \DOMNode */
        foreach ($xpath->query('/config/mapping/resource') as $mappingNode) {
            $mappingData = array();
            $mappingAttributes = $mappingNode->attributes;
            $idNode = $mappingAttributes->getNamedItem('id');
            if (is_null($idNode)) {
                throw new \Exception('Attribute "id" is required for ACL resource mapping.');
            }
            $mappingData['id'] = $idNode->nodeValue;

            $parentNode = $mappingAttributes->getNamedItem('parent');
            if (is_null($parentNode)) {
                throw new \Exception('Attribute "parent" is required for ACL resource mapping.');
            }
            $mappingData['parent'] = $parentNode->nodeValue;
            $aclResourceConfig['config']['mapping'][] = $mappingData;
        }
        return $aclResourceConfig;
    }
}

