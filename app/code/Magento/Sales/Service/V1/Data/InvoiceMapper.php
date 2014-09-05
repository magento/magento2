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
namespace Magento\Sales\Service\V1\Data;

/**
 * Class InvoiceMapper
 */
class InvoiceMapper
{
    /**
     * @var InvoiceBuilder
     */
    protected $invoiceBuilder;

    /**
     * @var InvoiceItemMapper
     */
    protected $invoiceItemMapper;

    /**
     * @param InvoiceBuilder $invoiceBuilder
     * @param InvoiceItemMapper $invoiceItemMapper
     */
    public function __construct(InvoiceBuilder $invoiceBuilder, InvoiceItemMapper $invoiceItemMapper)
    {
        $this->invoiceBuilder = $invoiceBuilder;
        $this->invoiceItemMapper = $invoiceItemMapper;
    }

    /**
     * Returns array of items
     *
     * @param \Magento\Sales\Model\Order\Invoice $object
     * @return InvoiceItem[]
     */
    protected function getItems(\Magento\Sales\Model\Order\Invoice $object)
    {
        $items = [];
        foreach ($object->getAllItems() as $item) {
            $items[] = $this->invoiceItemMapper->extractDto($item);
        }
        return $items;
    }

    /**
     * @param \Magento\Sales\Model\Order\Invoice $object
     * @return \Magento\Framework\Service\Data\AbstractExtensibleObject
     */
    public function extractDto(\Magento\Sales\Model\Order\Invoice $object)
    {
        $this->invoiceBuilder->populateWithArray($object->getData());
        $this->invoiceBuilder->setItems($this->getItems($object));
        return $this->invoiceBuilder->create();
    }
}
