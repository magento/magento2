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

/**
 * Factory class for \Magento\Sales\Model\Quote\Address\Total\AbstractTotal
 */
namespace Magento\Sales\Model\Quote\Address;

class TotalFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager = null;

    /**
     * Quote address factory constructor
     *
     * @param \Magento\Framework\ObjectManager $objManager
     */
    public function __construct(\Magento\Framework\ObjectManager $objManager)
    {
        $this->_objectManager = $objManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param string $instanceName
     * @param array $data
     * @return \Magento\Sales\Model\Quote\Address\Total\AbstractTotal
     */
    public function create($instanceName, array $data = array())
    {
        return $this->_objectManager->create($instanceName, $data);
    }
}
