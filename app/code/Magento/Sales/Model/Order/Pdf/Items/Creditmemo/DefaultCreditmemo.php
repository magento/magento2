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
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales Order Creditmemo Pdf default items renderer
 *
 * @category   Magento
 * @package    Magento_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sales\Model\Order\Pdf\Items\Creditmemo;

class DefaultCreditmemo extends \Magento\Sales\Model\Order\Pdf\Items\AbstractItems
{
    /**
     * Core string
     *
     * @var \Magento\Core\Helper\String
     */
    protected $_coreString = null;

    /**
     * @param \Magento\Core\Helper\String $coreString
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\App\Dir $coreDir
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\String $coreString,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\App\Dir $coreDir,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_coreString = $coreString;
        parent::__construct($taxData, $context, $registry, $coreDir, $resource, $resourceCollection, $data);
    }

    /**
     * Draw process
     */
    public function draw()
    {
        $order  = $this->getOrder();
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();
        $lines  = array();

        // draw Product name
        $lines[0] = array(array(
            'text' => $this->_coreString->strSplit($item->getName(), 35, true, true),
            'feed' => 35,
        ));

        // draw SKU
        $lines[0][] = array(
            'text'  => $this->_coreString->strSplit($this->getSku($item), 17),
            'feed'  => 255,
            'align' => 'right'
        );

        // draw Total (ex)
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getRowTotal()),
            'feed'  => 330,
            'font'  => 'bold',
            'align' => 'right',
        );

        // draw Discount
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt(-$item->getDiscountAmount()),
            'feed'  => 380,
            'font'  => 'bold',
            'align' => 'right'
        );

        // draw QTY
        $lines[0][] = array(
            'text'  => $item->getQty() * 1,
            'feed'  => 445,
            'font'  => 'bold',
            'align' => 'right',
        );

        // draw Tax
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getTaxAmount()),
            'feed'  => 495,
            'font'  => 'bold',
            'align' => 'right'
        );

        // draw Total (inc)
        $subtotal = $item->getRowTotal() + $item->getTaxAmount() + $item->getHiddenTaxAmount()
            - $item->getDiscountAmount();
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($subtotal),
            'feed'  => 565,
            'font'  => 'bold',
            'align' => 'right'
        );

        // draw options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = array(
                    'text' => $this->_coreString->strSplit(strip_tags($option['label']), 40, true, true),
                    'font' => 'italic',
                    'feed' => 35
                );

                // draw options value
                $_printValue = isset($option['print_value']) ? $option['print_value'] : strip_tags($option['value']);
                $lines[][] = array(
                    'text' => $this->_coreString->strSplit($_printValue, 30, true, true),
                    'feed' => 40
                );
            }
        }

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 20
        );

        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $this->setPage($page);
    }
}
