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
namespace Magento\Payment\Model\Cart\SalesModel;

/**
 * Factory for creating payment cart sales models
 */
class Factory
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Wrap sales model with Magento\Payment\Model\Cart\SalesModel\SalesModelInterface
     *
     * @param \Magento\Sales\Model\Order|\Magento\Sales\Model\Quote $salesModel
     * @return \Magento\Payment\Model\Cart\SalesModel\SalesModelInterface
     * @throws \InvalidArgumentException
     */
    public function create($salesModel)
    {
        $arguments = array('salesModel' => $salesModel);
        if ($salesModel instanceof \Magento\Sales\Model\Quote) {
            return $this->_objectManager->create('Magento\Payment\Model\Cart\SalesModel\Quote', $arguments);
        } else if ($salesModel instanceof \Magento\Sales\Model\Order) {
            return $this->_objectManager->create('Magento\Payment\Model\Cart\SalesModel\Order', $arguments);
        }
        throw new \InvalidArgumentException('Sales model has bad type!');
    }
}
