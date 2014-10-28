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
namespace Magento\Sales\Service\V1\Action;

use Magento\Sales\Service\V1\Data\ShipmentConverter;

/**
 * Class ShipmentCreate
 *
 */
class ShipmentCreate
{
    /**
     * @var ShipmentConverter
     */
    protected $shipmentConverter;

    /**
     * @var \Magento\Framework\Logger
     */
    protected $logger;

    /**
     * @param ShipmentConverter $shipmentConverter
     * @param \Magento\Framework\Logger $logger
     */
    public function __construct(ShipmentConverter $shipmentConverter, \Magento\Framework\Logger $logger)
    {
        $this->shipmentConverter = $shipmentConverter;
        $this->logger = $logger;
    }

    /**
     * Invoke CreateShipment service
     *
     * @param \Magento\Sales\Service\V1\Data\Shipment $shipmentDataObject
     * @return bool
     * @throws \Exception
     */
    public function invoke(\Magento\Sales\Service\V1\Data\Shipment $shipmentDataObject)
    {
        try {
            /** @var \Magento\Sales\Model\Order\Shipment $shipment */
            $shipment = $this->shipmentConverter->getModel($shipmentDataObject);
            if (!$shipment) {
                return false;
            }
            $shipment->getOrder()->setIsInProcess(true);
            $shipment->register();
            $shipment->save();
            return true;
        } catch (\Exception $e) {
            $this->logger->logException($e);
            throw new \Exception(__('An error has occurred during creating Shipment'));
        }
    }
}
