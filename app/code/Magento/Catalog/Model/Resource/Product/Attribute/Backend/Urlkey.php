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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Resource\Product\Attribute\Backend;

/**
 * Product url key attribute backend
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Urlkey extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Catalog url
     *
     * @var \Magento\Catalog\Model\Url
     */
    protected $_catalogUrl;

    /**
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Catalog\Model\Url $catalogUrl
     */
    public function __construct(\Magento\Framework\Logger $logger, \Magento\Catalog\Model\Url $catalogUrl)
    {
        $this->_catalogUrl = $catalogUrl;
        parent::__construct($logger);
    }

    /**
     * Before save
     *
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $attributeName = $this->getAttribute()->getName();

        $urlKey = $object->getData($attributeName);
        if ($urlKey == '') {
            $urlKey = $object->getName();
        }

        $object->setData($attributeName, $object->formatUrlKey($urlKey));

        return $this;
    }

    /**
     * Refresh product rewrites
     *
     * @param \Magento\Framework\Object $object
     * @return $this
     */
    public function afterSave($object)
    {
        if ($object->dataHasChangedFor($this->getAttribute()->getName())) {
            $this->_catalogUrl->refreshProductRewrites(null, $object, true);
        }
        return $this;
    }
}
