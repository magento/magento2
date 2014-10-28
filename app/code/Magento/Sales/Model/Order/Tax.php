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
namespace Magento\Sales\Model\Order;

/**
 *
 * @method \Magento\Sales\Model\Resource\Order\Tax _getResource()
 * @method \Magento\Sales\Model\Resource\Order\Tax getResource()
 * @method int getOrderId()
 * @method \Magento\Sales\Model\Order\Tax setOrderId(int $value)
 * @method string getCode()
 * @method \Magento\Sales\Model\Order\Tax setCode(string $value)
 * @method string getTitle()
 * @method \Magento\Sales\Model\Order\Tax setTitle(string $value)
 * @method float getPercent()
 * @method \Magento\Sales\Model\Order\Tax setPercent(float $value)
 * @method float getAmount()
 * @method \Magento\Sales\Model\Order\Tax setAmount(float $value)
 * @method int getPriority()
 * @method \Magento\Sales\Model\Order\Tax setPriority(int $value)
 * @method int getPosition()
 * @method \Magento\Sales\Model\Order\Tax setPosition(int $value)
 * @method float getBaseAmount()
 * @method \Magento\Sales\Model\Order\Tax setBaseAmount(float $value)
 * @method int getProcess()
 * @method \Magento\Sales\Model\Order\Tax setProcess(int $value)
 * @method float getBaseRealAmount()
 * @method \Magento\Sales\Model\Order\Tax setBaseRealAmount(float $value)
 * @method int getHidden()
 * @method \Magento\Sales\Model\Order\Tax setHidden(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Tax extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Resource\Order\Tax');
    }
}
