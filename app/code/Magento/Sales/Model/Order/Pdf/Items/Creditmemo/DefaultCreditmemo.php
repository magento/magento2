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
namespace Magento\Sales\Model\Order\Pdf\Items\Creditmemo;

/**
 * Sales Order Creditmemo Pdf default items renderer
 */
class DefaultCreditmemo extends \Magento\Sales\Model\Order\Pdf\Items\AbstractItems
{
    /**
     * Core string
     *
     * @var \Magento\Framework\Stdlib\String
     */
    protected $string;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Stdlib\String $string
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Stdlib\String $string,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->string = $string;
        parent::__construct(
            $context,
            $registry,
            $taxData,
            $filesystem,
            $filterManager,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Draw process
     *
     * @return void
     */
    public function draw()
    {
        $order = $this->getOrder();
        $item = $this->getItem();
        $pdf = $this->getPdf();
        $page = $this->getPage();
        $lines = array();

        // draw Product name
        $lines[0] = array(array('text' => $this->string->split($item->getName(), 35, true, true), 'feed' => 35));

        // draw SKU
        $lines[0][] = array(
            'text' => $this->string->split($this->getSku($item), 17),
            'feed' => 255,
            'align' => 'right'
        );

        // draw Total (ex)
        $lines[0][] = array(
            'text' => $order->formatPriceTxt($item->getRowTotal()),
            'feed' => 330,
            'font' => 'bold',
            'align' => 'right'
        );

        // draw Discount
        $lines[0][] = array(
            'text' => $order->formatPriceTxt(-$item->getDiscountAmount()),
            'feed' => 380,
            'font' => 'bold',
            'align' => 'right'
        );

        // draw QTY
        $lines[0][] = array('text' => $item->getQty() * 1, 'feed' => 445, 'font' => 'bold', 'align' => 'right');

        // draw Tax
        $lines[0][] = array(
            'text' => $order->formatPriceTxt($item->getTaxAmount()),
            'feed' => 495,
            'font' => 'bold',
            'align' => 'right'
        );

        // draw Total (inc)
        $subtotal = $item->getRowTotal() +
            $item->getTaxAmount() +
            $item->getHiddenTaxAmount() -
            $item->getDiscountAmount();
        $lines[0][] = array(
            'text' => $order->formatPriceTxt($subtotal),
            'feed' => 565,
            'font' => 'bold',
            'align' => 'right'
        );

        // draw options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = array(
                    'text' => $this->string->split($this->filterManager->stripTags($option['label']), 40, true, true),
                    'font' => 'italic',
                    'feed' => 35
                );

                // draw options value
                $printValue = isset(
                    $option['print_value']
                ) ? $option['print_value'] : $this->filterManager->stripTags(
                    $option['value']
                );
                $lines[][] = array('text' => $this->string->split($printValue, 30, true, true), 'feed' => 40);
            }
        }

        $lineBlock = array('lines' => $lines, 'height' => 20);

        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $this->setPage($page);
    }
}
