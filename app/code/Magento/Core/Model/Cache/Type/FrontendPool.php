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
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * In-memory readonly pool of cache front-ends with enforced access control, specific to cache types
 */
namespace Magento\Core\Model\Cache\Type;

class FrontendPool
{
    /**
     * @var \Magento\ObjectManager
     */
    private $_objectManager;

    /**
     * @var \Magento\Core\Model\Cache\Frontend\Pool
     */
    private $_frontendPool;

    /**
     * @var \Magento\Cache\FrontendInterface[]
     */
    private $_instances = array();

    /**
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\Core\Model\Cache\Frontend\Pool $frontendPool
     */
    public function __construct(
        \Magento\ObjectManager $objectManager,
        \Magento\Core\Model\Cache\Frontend\Pool $frontendPool
    ) {
        $this->_objectManager = $objectManager;
        $this->_frontendPool = $frontendPool;
    }

    /**
     * Retrieve cache frontend instance by its unique identifier, enforcing identifier-scoped access control
     *
     * @param string $identifier Cache frontend identifier
     * @return \Magento\Cache\FrontendInterface Cache frontend instance
     */
    public function get($identifier)
    {
        if (!isset($this->_instances[$identifier])) {
            $frontendInstance = $this->_frontendPool->get($identifier);
            if (!$frontendInstance) {
                $frontendInstance = $this->_frontendPool->get(
                    \Magento\Core\Model\Cache\Frontend\Pool::DEFAULT_FRONTEND_ID
                );
            }
            /** @var $frontendInstance \Magento\Core\Model\Cache\Type\AccessProxy */
            $frontendInstance = $this->_objectManager->create(
                'Magento\Core\Model\Cache\Type\AccessProxy', array(
                    'frontend' => $frontendInstance,
                    'identifier' => $identifier,
                )
            );
            $this->_instances[$identifier] = $frontendInstance;
        }
        return $this->_instances[$identifier];
    }
}
