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
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Url rewrite suffix backend
 */
namespace Magento\Catalog\Model\System\Config\Backend\Catalog\Url\Rewrite;

class Suffix extends \Magento\Core\Model\Config\Value
{
    /**
     * Core url rewrite
     *
     * @var \Magento\Core\Helper\Url\Rewrite
     */
    protected $_coreUrlRewrite = null;

    /**
     * @param \Magento\Core\Helper\Url\Rewrite $coreUrlRewrite
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Core\Model\Config $config
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Url\Rewrite $coreUrlRewrite,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Model\Config $config,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_coreUrlRewrite = $coreUrlRewrite;
        parent::__construct($context, $registry, $storeManager, $config, $resource, $resourceCollection, $data);
    }

    /**
     * Check url rewrite suffix - whether we can support it
     *
     * @return \Magento\Catalog\Model\System\Config\Backend\Catalog\Url\Rewrite\Suffix
     */
    protected function _beforeSave()
    {
        $this->_coreUrlRewrite->validateSuffix($this->getValue());
        return $this;
    }
}
