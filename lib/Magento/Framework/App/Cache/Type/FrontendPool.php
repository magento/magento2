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
namespace Magento\Framework\App\Cache\Type;

/**
 * In-memory readonly pool of cache front-ends with enforced access control, specific to cache types
 */
class FrontendPool
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    private $_objectManager;

    /**
     * @var \Magento\Framework\App\Arguments
     */
    private $_arguments;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    private $_frontendPool;

    /**
     * @var array
     */
    private $_typeFrontendMap;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface[]
     */
    private $_instances = array();

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param \Magento\Framework\App\Arguments $arguments
     * @param \Magento\Framework\App\Cache\Frontend\Pool $frontendPool
     * @param array $typeFrontendMap Format: array('<cache_type_id>' => '<cache_frontend_id>', ...)
     */
    public function __construct(
        \Magento\Framework\ObjectManager $objectManager,
        \Magento\Framework\App\Arguments $arguments,
        \Magento\Framework\App\Cache\Frontend\Pool $frontendPool,
        array $typeFrontendMap = array()
    ) {
        $this->_objectManager = $objectManager;
        $this->_arguments = $arguments;
        $this->_frontendPool = $frontendPool;
        $this->_typeFrontendMap = $typeFrontendMap;
    }

    /**
     * Retrieve cache frontend instance by a cache type identifier, enforcing identifier-scoped access control
     *
     * @param string $cacheType Cache type identifier
     * @return \Magento\Framework\Cache\FrontendInterface Cache frontend instance
     */
    public function get($cacheType)
    {
        if (!isset($this->_instances[$cacheType])) {
            $frontendId = $this->_getCacheFrontendId($cacheType);
            $frontendInstance = $this->_frontendPool->get($frontendId);
            /** @var $frontendInstance AccessProxy */
            $frontendInstance = $this->_objectManager->create(
                'Magento\Framework\App\Cache\Type\AccessProxy',
                array('frontend' => $frontendInstance, 'identifier' => $cacheType)
            );
            $this->_instances[$cacheType] = $frontendInstance;
        }
        return $this->_instances[$cacheType];
    }

    /**
     * Retrieve cache frontend identifier, associated with a cache type
     *
     * @param string $cacheType
     * @return string
     */
    protected function _getCacheFrontendId($cacheType)
    {
        $result = $this->_arguments->getCacheTypeFrontendId($cacheType);
        if (!$result) {
            if (isset($this->_typeFrontendMap[$cacheType])) {
                $result = $this->_typeFrontendMap[$cacheType];
            } else {
                $result = \Magento\Framework\App\Cache\Frontend\Pool::DEFAULT_FRONTEND_ID;
            }
        }
        return $result;
    }
}
