<?php
/**
 * Factory or authentication objects
 *
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
 *
 */
namespace Magento\Outbound\Authentication;

use Magento\ObjectManager;
use Magento\Outbound\AuthenticationInterface;

class Factory
{
    /**
     * @var ObjectManager
     */
    private $_objectManager;

    /**
     * @var array representing the map for authentications and authentication classes
     */
    protected $_authenticationMap = array();

    /**
     * @param array $authenticationMap
     * @param ObjectManager $objectManager
     */
    public function __construct(
        array $authenticationMap,
        ObjectManager $objectManager
    ) {
        $this->_authenticationMap = $authenticationMap;
        $this->_objectManager = $objectManager;
    }

    /**
     * Returns an Authentication that matches the type specified within Endpoint
     *
     * @param string $authenticationType
     * @return AuthenticationInterface
     * @throws \LogicException
     */
    public function getAuthentication($authenticationType)
    {
        if (!isset($this->_authenticationMap[$authenticationType])) {
            throw new \LogicException("There is no authentication for the type given: {$authenticationType}");
        }

        $authentication =  $this->_objectManager->get($this->_authenticationMap[$authenticationType]);
        if (!$authentication instanceof AuthenticationInterface) {
            throw new \LogicException(
                "Authentication class for {$authenticationType} does not implement authentication interface"
            );
        }
        return $authentication;
    }

}
