<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Pdf\Items;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Sales Order Pdf Items renderer Abstract
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
abstract class AbstractItems extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Order model
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * Source model (invoice, shipment, creditmemo)
     *
     * @var \Magento\Framework\Model\AbstractModel
     */
    protected $_source;

    /**
     * Item object
     *
     * @var \Magento\Framework\DataObject
     */
    protected $_item;

    /**
     * Pdf object
     *
     * @var \Magento\Sales\Model\Order\Pdf\AbstractPdf
     */
    protected $_pdf;

    /**
     * Pdf current page
     *
     * @var \Zend_Pdf_Page
     */
    protected $_pdfPage;

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $_rootDirectory;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\Filesystem $filesystem ,
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->filterManager = $filterManager;
        $this->_taxData = $taxData;
        $this->_rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Set order model
     *
     * @param  \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function setOrder(\Magento\Sales\Model\Order $order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Set Source model
     *
     * @param  \Magento\Framework\Model\AbstractModel $source
     * @return $this
     */
    public function setSource(\Magento\Framework\Model\AbstractModel $source)
    {
        $this->_source = $source;
        return $this;
    }

    /**
     * Set item object
     *
     * @param  \Magento\Framework\DataObject $item
     * @return $this
     */
    public function setItem(\Magento\Framework\DataObject $item)
    {
        $this->_item = $item;
        return $this;
    }

    /**
     * Set Pdf model
     *
     * @param  \Magento\Sales\Model\Order\Pdf\AbstractPdf $pdf
     * @return $this
     */
    public function setPdf(\Magento\Sales\Model\Order\Pdf\AbstractPdf $pdf)
    {
        $this->_pdf = $pdf;
        return $this;
    }

    /**
     * Set current page
     *
     * @param  \Zend_Pdf_Page $page
     * @return $this
     */
    public function setPage(\Zend_Pdf_Page $page)
    {
        $this->_pdfPage = $page;
        return $this;
    }

    /**
     * Retrieve order object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if (null === $this->_order) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The order object is not specified.'));
        }
        return $this->_order;
    }

    /**
     * Retrieve source object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function getSource()
    {
        if (null === $this->_source) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The source object is not specified.'));
        }
        return $this->_source;
    }

    /**
     * Retrieve item object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\DataObject
     */
    public function getItem()
    {
        if (null === $this->_item) {
            throw new \Magento\Framework\Exception\LocalizedException(__('An item object is not specified.'));
        }
        return $this->_item;
    }

    /**
     * Retrieve Pdf model
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Sales\Model\Order\Pdf\AbstractPdf
     */
    public function getPdf()
    {
        if (null === $this->_pdf) {
            throw new \Magento\Framework\Exception\LocalizedException(__('A PDF object is not specified.'));
        }
        return $this->_pdf;
    }

    /**
     * Retrieve Pdf page object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Zend_Pdf_Page
     */
    public function getPage()
    {
        if (null === $this->_pdfPage) {
            throw new \Magento\Framework\Exception\LocalizedException(__('A PDF page object is not specified.'));
        }
        return $this->_pdfPage;
    }

    /**
     * Draw item line
     *
     * @return void
     */
    abstract public function draw();

    /**
     * Format option value process
     *
     * @param array|string $value
     * @return string
     */
    protected function _formatOptionValue($value)
    {
        $order = $this->getOrder();

        $resultValue = '';
        if (is_array($value)) {
            if (isset($value['qty'])) {
                $resultValue .= $this->filterManager->sprintf($value['qty'], ['format' => '%d']) . ' x ';
            }

            $resultValue .= $value['title'];

            if (isset($value['price'])) {
                $resultValue .= " " . $order->formatPrice($value['price']);
            }
            return $resultValue;
        } else {
            return $value;
        }
    }

    /**
     * Get array of arrays with item prices information for display in PDF
     *
     * Format: array(
     *  $index => array(
     *      'label'    => $label,
     *      'price'    => $price,
     *      'subtotal' => $subtotal
     *  )
     * )
     *
     * @return array
     */
    public function getItemPricesForDisplay()
    {
        $order = $this->getOrder();
        $item = $this->getItem();
        if ($this->_taxData->displaySalesBothPrices()) {
            $prices = [
                [
                    'label' => __('Excl. Tax') . ':',
                    'price' => $order->formatPriceTxt($item->getPrice()),
                    'subtotal' => $order->formatPriceTxt($item->getRowTotal()),
                ],
                [
                    'label' => __('Incl. Tax') . ':',
                    'price' => $order->formatPriceTxt($item->getPriceInclTax()),
                    'subtotal' => $order->formatPriceTxt($item->getRowTotalInclTax())
                ],
            ];
        } elseif ($this->_taxData->displaySalesPriceInclTax()) {
            $prices = [
                [
                    'price' => $order->formatPriceTxt($item->getPriceInclTax()),
                    'subtotal' => $order->formatPriceTxt($item->getRowTotalInclTax()),
                ],
            ];
        } else {
            $prices = [
                [
                    'price' => $order->formatPriceTxt($item->getPrice()),
                    'subtotal' => $order->formatPriceTxt($item->getRowTotal()),
                ],
            ];
        }
        return $prices;
    }

    /**
     * Retrieve item options
     *
     * @return array
     */
    public function getItemOptions()
    {
        $result = [];
        $options = $this->getItem()->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }
        return $result;
    }

    /**
     * Set font as regular
     *
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontRegular($size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/GnuFreeFont/FreeSerif.ttf')
        );
        $this->getPage()->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as bold
     *
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontBold($size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/GnuFreeFont/FreeSerifBold.ttf')
        );
        $this->getPage()->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as italic
     *
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontItalic($size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/GnuFreeFont/FreeSerifItalic.ttf')
        );
        $this->getPage()->setFont($font, $size);
        return $font;
    }

    /**
     * Return item Sku
     *
     * @param mixed $item
     * @return mixed
     */
    public function getSku($item)
    {
        if ($item->getOrderItem()->getProductOptionByCode('simple_sku')) {
            return $item->getOrderItem()->getProductOptionByCode('simple_sku');
        } else {
            return $item->getSku();
        }
    }
}
