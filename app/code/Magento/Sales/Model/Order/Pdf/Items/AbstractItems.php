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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Order\Pdf\Items;

/**
 * Sales Order Pdf Items renderer Abstract
 */
abstract class AbstractItems extends \Magento\Model\AbstractModel
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
     * @var \Magento\Model\AbstractModel
     */
    protected $_source;

    /**
     * Item object
     *
     * @var \Magento\Object
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
     * @var \Magento\Filesystem\Directory\ReadInterface
     */
    protected $_rootDirectory;

    /**
     * @var \Magento\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\App\Filesystem $filesystem ,
     * @param \Magento\Filter\FilterManager $filterManager
     * @param \Magento\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\App\Filesystem $filesystem,
        \Magento\Filter\FilterManager $filterManager,
        \Magento\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->filterManager = $filterManager;
        $this->_taxData = $taxData;
        $this->_rootDirectory = $filesystem->getDirectoryRead(\Magento\App\Filesystem::ROOT_DIR);
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
     * @param  \Magento\Model\AbstractModel $source
     * @return $this
     */
    public function setSource(\Magento\Model\AbstractModel $source)
    {
        $this->_source = $source;
        return $this;
    }

    /**
     * Set item object
     *
     * @param  \Magento\Object $item
     * @return $this
     */
    public function setItem(\Magento\Object $item)
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
     * @throws \Magento\Model\Exception
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if (null === $this->_order) {
            throw new \Magento\Model\Exception(__('The order object is not specified.'));
        }
        return $this->_order;
    }

    /**
     * Retrieve source object
     *
     * @throws \Magento\Model\Exception
     * @return \Magento\Model\AbstractModel
     */
    public function getSource()
    {
        if (null === $this->_source) {
            throw new \Magento\Model\Exception(__('The source object is not specified.'));
        }
        return $this->_source;
    }

    /**
     * Retrieve item object
     *
     * @throws \Magento\Model\Exception
     * @return \Magento\Object
     */
    public function getItem()
    {
        if (null === $this->_item) {
            throw new \Magento\Model\Exception(__('An item object is not specified.'));
        }
        return $this->_item;
    }

    /**
     * Retrieve Pdf model
     *
     * @throws \Magento\Model\Exception
     * @return \Magento\Sales\Model\Order\Pdf\AbstractPdf
     */
    public function getPdf()
    {
        if (null === $this->_pdf) {
            throw new \Magento\Model\Exception(__('A PDF object is not specified.'));
        }
        return $this->_pdf;
    }

    /**
     * Retrieve Pdf page object
     *
     * @throws \Magento\Model\Exception
     * @return \Zend_Pdf_Page
     */
    public function getPage()
    {
        if (null === $this->_pdfPage) {
            throw new \Magento\Model\Exception(__('A PDF page object is not specified.'));
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
                $resultValue .= $this->filterManager->sprintf($value['qty'], array('format' => '%d')) . ' x ';
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
            $prices = array(
                array(
                    'label' => __('Excl. Tax') . ':',
                    'price' => $order->formatPriceTxt($item->getPrice()),
                    'subtotal' => $order->formatPriceTxt($item->getRowTotal())
                ),
                array(
                    'label' => __('Incl. Tax') . ':',
                    'price' => $order->formatPriceTxt($item->getPriceInclTax()),
                    'subtotal' => $order->formatPriceTxt($item->getRowTotalInclTax())
                )
            );
        } elseif ($this->_taxData->displaySalesPriceInclTax()) {
            $prices = array(
                array(
                    'price' => $order->formatPriceTxt($item->getPriceInclTax()),
                    'subtotal' => $order->formatPriceTxt($item->getRowTotalInclTax())
                )
            );
        } else {
            $prices = array(
                array(
                    'price' => $order->formatPriceTxt($item->getPrice()),
                    'subtotal' => $order->formatPriceTxt($item->getRowTotal())
                )
            );
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
        $result = array();
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
            $this->_rootDirectory->getAbsolutePath('lib/LinLibertineFont/LinLibertine_Re-4.4.1.ttf')
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
            $this->_rootDirectory->getAbsolutePath('lib/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf')
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
            $this->_rootDirectory->getAbsolutePath('lib/LinLibertineFont/LinLibertine_It-2.8.2.ttf')
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
