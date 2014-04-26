<?php
/**
 * ACL block filter
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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Model\Layout\Filter;

class Acl
{
    /**
     * Authorization
     *
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @param \Magento\Framework\AuthorizationInterface $authorization
     */
    public function __construct(\Magento\Framework\AuthorizationInterface $authorization)
    {
        $this->_authorization = $authorization;
    }

    /**
     * Delete nodes that have "acl" attribute but value is "not allowed"
     * In any case, the "acl" attribute will be unset
     *
     * @param \Magento\Framework\Simplexml\Element $xml
     * @return void
     */
    public function filterAclNodes(\Magento\Framework\Simplexml\Element $xml)
    {
        $limitations = $xml->xpath('//*[@acl]') ?: array();
        foreach ($limitations as $node) {
            if (!$this->_authorization->isAllowed($node['acl'])) {
                $node->unsetSelf();
            } else {
                unset($node['acl']);
            }
        }
    }
}
