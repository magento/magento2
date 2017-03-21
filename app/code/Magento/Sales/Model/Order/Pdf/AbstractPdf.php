<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Model\Order\Pdf;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Sales Order PDF abstract model
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractPdf extends \Magento\Framework\DataObject
{
    /**
     * Y coordinate
     *
     * @var int
     */
    public $y;

    /**
     * Item renderers with render type key
     * model    => the model name
     * renderer => the renderer model
     *
     * @var array
     */
    protected $_renderers = [];

    /**
     * Predefined constants
     */
    const XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID = 'sales_pdf/invoice/put_order_id';

    const XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID = 'sales_pdf/shipment/put_order_id';

    const XML_PATH_SALES_PDF_CREDITMEMO_PUT_ORDER_ID = 'sales_pdf/creditmemo/put_order_id';

    /**
     * Zend PDF object
     *
     * @var \Zend_Pdf
     */
    protected $_pdf;

    /**
     * Retrieve PDF
     *
     * @return \Zend_Pdf
     */
    abstract public function getPdf();

    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentData;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $_rootDirectory;

    /**
     * @var Config
     */
    protected $_pdfConfig;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Total\Factory
     */
    protected $_pdfTotalFactory;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\ItemsFactory
     */
    protected $_pdfItemsFactory;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    protected $addressRenderer;

    /**
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param Config $pdfConfig
     * @param Total\Factory $pdfTotalFactory
     * @param ItemsFactory $pdfItemsFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        Config $pdfConfig,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        array $data = []
    ) {
        $this->addressRenderer = $addressRenderer;
        $this->_paymentData = $paymentData;
        $this->_localeDate = $localeDate;
        $this->string = $string;
        $this->_scopeConfig = $scopeConfig;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->_pdfConfig = $pdfConfig;
        $this->_pdfTotalFactory = $pdfTotalFactory;
        $this->_pdfItemsFactory = $pdfItemsFactory;
        $this->inlineTranslation = $inlineTranslation;
        parent::__construct($data);
    }

    /**
     * Returns the total width in points of the string using the specified font and
     * size.
     *
     * This is not the most efficient way to perform this calculation. I'm
     * concentrating optimization efforts on the upcoming layout manager class.
     * Similar calculations exist inside the layout manager class, but widths are
     * generally calculated only after determining line fragments.
     *
     * @param  string $string
     * @param  \Zend_Pdf_Resource_Font $font
     * @param  float $fontSize Font size in points
     * @return float
     */
    public function widthForStringUsingFontSize($string, $font, $fontSize)
    {
        $drawingString = '"libiconv"' == ICONV_IMPL ? iconv(
            'UTF-8',
            'UTF-16BE//IGNORE',
            $string
        ) : @iconv(
            'UTF-8',
            'UTF-16BE',
            $string
        );

        $characters = [];
        for ($i = 0; $i < strlen($drawingString); $i++) {
            $characters[] = ord($drawingString[$i++]) << 8 | ord($drawingString[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = array_sum($widths) / $font->getUnitsPerEm() * $fontSize;
        return $stringWidth;
    }

    /**
     * Calculate coordinates to draw something in a column aligned to the right
     *
     * @param  string $string
     * @param  int $x
     * @param  int $columnWidth
     * @param  \Zend_Pdf_Resource_Font $font
     * @param  int $fontSize
     * @param  int $padding
     * @return int
     */
    public function getAlignRight($string, $x, $columnWidth, \Zend_Pdf_Resource_Font $font, $fontSize, $padding = 5)
    {
        $width = $this->widthForStringUsingFontSize($string, $font, $fontSize);
        return $x + $columnWidth - $width - $padding;
    }

    /**
     * Calculate coordinates to draw something in a column aligned to the center
     *
     * @param  string $string
     * @param  int $x
     * @param  int $columnWidth
     * @param  \Zend_Pdf_Resource_Font $font
     * @param  int $fontSize
     * @return int
     */
    public function getAlignCenter($string, $x, $columnWidth, \Zend_Pdf_Resource_Font $font, $fontSize)
    {
        $width = $this->widthForStringUsingFontSize($string, $font, $fontSize);
        return $x + round(($columnWidth - $width) / 2);
    }

    /**
     * Insert logo to pdf page
     *
     * @param \Zend_Pdf_Page &$page
     * @param null $store
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function insertLogo(&$page, $store = null)
    {
        $this->y = $this->y ? $this->y : 815;
        $image = $this->_scopeConfig->getValue(
            'sales/identity/logo',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($image) {
            $imagePath = '/sales/store/logo/' . $image;
            if ($this->_mediaDirectory->isFile($imagePath)) {
                $image = \Zend_Pdf_Image::imageWithPath($this->_mediaDirectory->getAbsolutePath($imagePath));
                $top = 830;
                //top border of the page
                $widthLimit = 270;
                //half of the page width
                $heightLimit = 270;
                //assuming the image is not a "skyscraper"
                $width = $image->getPixelWidth();
                $height = $image->getPixelHeight();

                //preserving aspect ratio (proportions)
                $ratio = $width / $height;
                if ($ratio > 1 && $width > $widthLimit) {
                    $width = $widthLimit;
                    $height = $width / $ratio;
                } elseif ($ratio < 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $height * $ratio;
                } elseif ($ratio == 1 && $height > $heightLimit) {
                    $height = $heightLimit;
                    $width = $widthLimit;
                }

                $y1 = $top - $height;
                $y2 = $top;
                $x1 = 25;
                $x2 = $x1 + $width;

                //coordinates after transformation are rounded by Zend
                $page->drawImage($image, $x1, $y1, $x2, $y2);

                $this->y = $y1 - 10;
            }
        }
    }

    /**
     * Insert address to pdf page
     *
     * @param \Zend_Pdf_Page &$page
     * @param null $store
     * @return void
     */
    protected function insertAddress(&$page, $store = null)
    {
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $font = $this->_setFontRegular($page, 10);
        $page->setLineWidth(0);
        $this->y = $this->y ? $this->y : 815;
        $top = 815;
        foreach (explode(
                     "\n",
                     $this->_scopeConfig->getValue(
                         'sales/identity/address',
                         \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                         $store
                     )
                 ) as $value) {
            if ($value !== '') {
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $page->drawText(
                        trim(strip_tags($_value)),
                        $this->getAlignRight($_value, 130, 440, $font, 10),
                        $top,
                        'UTF-8'
                    );
                    $top -= 10;
                }
            }
        }
        $this->y = $this->y > $top ? $top : $this->y;
    }

    /**
     * Format address
     *
     * @param  string $address
     * @return array
     */
    protected function _formatAddress($address)
    {
        $return = [];
        foreach (explode('|', $address) as $str) {
            foreach ($this->string->split($str, 45, true, true) as $part) {
                if (empty($part)) {
                    continue;
                }
                $return[] = $part;
            }
        }
        return $return;
    }

    /**
     * Calculate address height
     *
     * @param  array $address
     * @return int Height
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _calcAddressHeight($address)
    {
        $y = 0;
        foreach ($address as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 55, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $y += 15;
                }
            }
        }
        return $y;
    }

    /**
     * Insert order to pdf page
     *
     * @param \Zend_Pdf_Page &$page
     * @param \Magento\Sales\Model\Order $obj
     * @param bool $putOrderId
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function insertOrder(&$page, $obj, $putOrderId = true)
    {
        if ($obj instanceof \Magento\Sales\Model\Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof \Magento\Sales\Model\Order\Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->drawRectangle(25, $top, 570, $top - 55);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->setDocHeaderCoordinates([25, $top, 570, $top - 55]);
        $this->_setFontRegular($page, 10);

        if ($putOrderId) {
            $page->drawText(__('Order # ') . $order->getRealOrderId(), 35, $top -= 30, 'UTF-8');
            $top +=15;
        }

        $top -=30;
        $page->drawText(
            __('Order Date: ') .
            $this->_localeDate->formatDate(
                $this->_localeDate->scopeDate(
                    $order->getStore(),
                    $order->getCreatedAt(),
                    true
                ),
                \IntlDateFormatter::MEDIUM,
                false
            ),
            35,
            $top,
            'UTF-8'
        );

        $top -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 275, $top - 25);
        $page->drawRectangle(275, $top, 570, $top - 25);

        /* Calculate blocks info */

        /* Billing Address */
        $billingAddress = $this->_formatAddress($this->addressRenderer->format($order->getBillingAddress(), 'pdf'));

        /* Payment */
        $paymentInfo = $this->_paymentData->getInfoBlock($order->getPayment())->setIsSecureMode(true)->toPdf();
        $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value) {
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress($this->addressRenderer->format($order->getShippingAddress(), 'pdf'));
            $shippingMethod = $order->getShippingDescription();
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 12);
        $page->drawText(__('Sold to:'), 35, $top - 15, 'UTF-8');

        if (!$order->getIsVirtual()) {
            $page->drawText(__('Ship to:'), 285, $top - 15, 'UTF-8');
        } else {
            $page->drawText(__('Payment Method:'), 285, $top - 15, 'UTF-8');
        }

        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, $top - 25, 570, $top - 33 - $addressesHeight);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;

        foreach ($billingAddress as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }

        $addressesEndY = $this->y;

        if (!$order->getIsVirtual()) {
            $this->y = $addressesStartY;
            foreach ($shippingAddress as $value) {
                if ($value !== '') {
                    $text = [];
                    foreach ($this->string->split($value, 45, true, true) as $_value) {
                        $text[] = $_value;
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
                        $this->y -= 15;
                    }
                }
            }

            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;

            $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 275, $this->y - 25);
            $page->drawRectangle(275, $this->y, 570, $this->y - 25);

            $this->y -= 15;
            $this->_setFontBold($page, 12);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $page->drawText(__('Payment Method'), 35, $this->y, 'UTF-8');
            $page->drawText(__('Shipping Method:'), 285, $this->y, 'UTF-8');

            $this->y -= 10;
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($page, 10);
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments = $this->y - 15;
        } else {
            $yPayments = $addressesStartY;
            $paymentLeft = 285;
        }

        foreach ($payment as $value) {
            if (trim($value) != '') {
                //Printing "Payment Method" lines
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $page->drawText(strip_tags(trim($_value)), $paymentLeft, $yPayments, 'UTF-8');
                    $yPayments -= 15;
                }
            }
        }

        if ($order->getIsVirtual()) {
            // replacement of Shipments-Payments rectangle block
            $yPayments = min($addressesEndY, $yPayments);
            $page->drawLine(25, $top - 25, 25, $yPayments);
            $page->drawLine(570, $top - 25, 570, $yPayments);
            $page->drawLine(25, $yPayments, 570, $yPayments);

            $this->y = $yPayments - 15;
        } else {
            $topMargin = 15;
            $methodStartY = $this->y;
            $this->y -= 15;

            foreach ($this->string->split($shippingMethod, 45, true, true) as $_value) {
                $page->drawText(strip_tags(trim($_value)), 285, $this->y, 'UTF-8');
                $this->y -= 15;
            }

            $yShipments = $this->y;
            $totalShippingChargesText = "(" . __(
                    'Total Shipping Charges'
                ) . " " . $order->formatPriceTxt(
                    $order->getShippingAmount()
                ) . ")";

            $page->drawText($totalShippingChargesText, 285, $yShipments - $topMargin, 'UTF-8');
            $yShipments -= $topMargin + 10;

            $tracks = [];
            if ($shipment) {
                $tracks = $shipment->getAllTracks();
            }
            if (count($tracks)) {
                $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->setLineWidth(0.5);
                $page->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                $page->drawLine(400, $yShipments, 400, $yShipments - 10);
                //$page->drawLine(510, $yShipments, 510, $yShipments - 10);

                $this->_setFontRegular($page, 9);
                $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                //$page->drawText(__('Carrier'), 290, $yShipments - 7 , 'UTF-8');
                $page->drawText(__('Title'), 290, $yShipments - 7, 'UTF-8');
                $page->drawText(__('Number'), 410, $yShipments - 7, 'UTF-8');

                $yShipments -= 20;
                $this->_setFontRegular($page, 8);
                foreach ($tracks as $track) {
                    $maxTitleLen = 45;
                    $endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                    $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                    $page->drawText($truncatedTitle, 292, $yShipments, 'UTF-8');
                    $page->drawText($track->getNumber(), 410, $yShipments, 'UTF-8');
                    $yShipments -= $topMargin - 5;
                }
            } else {
                $yShipments -= $topMargin - 5;
            }

            $currentY = min($yPayments, $yShipments);

            // replacement of Shipments-Payments rectangle block
            $page->drawLine(25, $methodStartY, 25, $currentY);
            //left
            $page->drawLine(25, $currentY, 570, $currentY);
            //bottom
            $page->drawLine(570, $currentY, 570, $methodStartY);
            //right

            $this->y = $currentY;
            $this->y -= 15;
        }
    }

    /**
     * Insert title and number for concrete document type
     *
     * @param  \Zend_Pdf_Page $page
     * @param  string $text
     * @return void
     */
    public function insertDocumentNumber(\Zend_Pdf_Page $page, $text)
    {
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->_setFontRegular($page, 10);
        $docHeader = $this->getDocHeaderCoordinates();
        $page->drawText($text, 35, $docHeader[1] - 15, 'UTF-8');
    }

    /**
     * Sort totals list
     *
     * @param  array $a
     * @param  array $b
     * @return int
     */
    protected function _sortTotalsList($a, $b)
    {
        if (!isset($a['sort_order']) || !isset($b['sort_order'])) {
            return 0;
        }

        if ($a['sort_order'] == $b['sort_order']) {
            return 0;
        }

        return $a['sort_order'] > $b['sort_order'] ? 1 : -1;
    }

    /**
     * Return total list
     *
     * @return \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal[] Array of totals
     */
    protected function _getTotalsList()
    {
        $totals = $this->_pdfConfig->getTotals();
        usort($totals, [$this, '_sortTotalsList']);
        $totalModels = [];
        foreach ($totals as $totalInfo) {
            $class = empty($totalInfo['model']) ? null : $totalInfo['model'];
            $totalModel = $this->_pdfTotalFactory->create($class);
            $totalModel->setData($totalInfo);
            $totalModels[] = $totalModel;
        }

        return $totalModels;
    }

    /**
     * Insert totals to pdf page
     *
     * @param  \Zend_Pdf_Page $page
     * @param  \Magento\Sales\Model\AbstractModel $source
     * @return \Zend_Pdf_Page
     */
    protected function insertTotals($page, $source)
    {
        $order = $source->getOrder();
        $totals = $this->_getTotalsList();
        $lineBlock = ['lines' => [], 'height' => 15];
        foreach ($totals as $total) {
            $total->setOrder($order)->setSource($source);

            if ($total->canDisplay()) {
                $total->setFontSize(10);
                foreach ($total->getTotalsForDisplay() as $totalData) {
                    $lineBlock['lines'][] = [
                        [
                            'text' => $totalData['label'],
                            'feed' => 475,
                            'align' => 'right',
                            'font_size' => $totalData['font_size'],
                            'font' => 'bold',
                        ],
                        [
                            'text' => $totalData['amount'],
                            'feed' => 565,
                            'align' => 'right',
                            'font_size' => $totalData['font_size'],
                            'font' => 'bold'
                        ],
                    ];
                }
            }
        }

        $this->y -= 20;
        $page = $this->drawLineBlocks($page, [$lineBlock]);
        return $page;
    }

    /**
     * Parse item description
     *
     * @param  \Magento\Framework\DataObject $item
     * @return array
     */
    protected function _parseItemDescription($item)
    {
        $matches = [];
        $description = $item->getDescription();
        if (preg_match_all('/<li.*?>(.*?)<\/li>/i', $description, $matches)) {
            return $matches[1];
        }

        return [$description];
    }

    /**
     * Before getPdf processing
     *
     * @return void
     */
    protected function _beforeGetPdf()
    {
        $this->inlineTranslation->suspend();
    }

    /**
     * After getPdf processing
     *
     * @return void
     */
    protected function _afterGetPdf()
    {
        $this->inlineTranslation->resume();
    }

    /**
     * Format option value process
     *
     * @param  array|string $value
     * @param  \Magento\Sales\Model\Order $order
     * @return string
     */
    protected function _formatOptionValue($value, $order)
    {
        $resultValue = '';
        if (is_array($value)) {
            if (isset($value['qty'])) {
                $resultValue .= sprintf('%d', $value['qty']) . ' x ';
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
     * Initialize renderer process
     *
     * @param string $type
     * @return void
     */
    protected function _initRenderer($type)
    {
        $rendererData = $this->_pdfConfig->getRenderersPerProduct($type);
        foreach ($rendererData as $productType => $renderer) {
            $this->_renderers[$productType] = ['model' => $renderer, 'renderer' => null];
        }
    }

    /**
     * Retrieve renderer model
     *
     * @param  string $type
     * @return \Magento\Sales\Model\Order\Pdf\Items\AbstractItems
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getRenderer($type)
    {
        if (!isset($this->_renderers[$type])) {
            $type = 'default';
        }

        if (!isset($this->_renderers[$type])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('We found an invalid renderer model.'));
        }

        if (is_null($this->_renderers[$type]['renderer'])) {
            $this->_renderers[$type]['renderer'] = $this->_pdfItemsFactory->get($this->_renderers[$type]['model']);
        }

        return $this->_renderers[$type]['renderer'];
    }

    /**
     * Public method of protected @see _getRenderer()
     *
     * Retrieve renderer model
     *
     * @param  string $type
     * @return \Magento\Sales\Model\Order\Pdf\Items\AbstractItems
     */
    public function getRenderer($type)
    {
        return $this->_getRenderer($type);
    }

    /**
     * Draw Item process
     *
     * @param  \Magento\Framework\DataObject $item
     * @param  \Zend_Pdf_Page $page
     * @param  \Magento\Sales\Model\Order $order
     * @return \Zend_Pdf_Page
     */
    protected function _drawItem(\Magento\Framework\DataObject $item, \Zend_Pdf_Page $page, \Magento\Sales\Model\Order $order)
    {
        $type = $item->getOrderItem()->getProductType();
        $renderer = $this->_getRenderer($type);
        $renderer->setOrder($order);
        $renderer->setItem($item);
        $renderer->setPdf($this);
        $renderer->setPage($page);
        $renderer->setRenderedModel($this);

        $renderer->draw();

        return $renderer->getPage();
    }

    /**
     * Set font as regular
     *
     * @param  \Zend_Pdf_Page $object
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontRegular($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Re-4.4.1.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as bold
     *
     * @param  \Zend_Pdf_Page $object
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontBold($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as italic
     *
     * @param  \Zend_Pdf_Page $object
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontItalic($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_It-2.8.2.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set PDF object
     *
     * @param  \Zend_Pdf $pdf
     * @return $this
     */
    protected function _setPdf(\Zend_Pdf $pdf)
    {
        $this->_pdf = $pdf;
        return $this;
    }

    /**
     * Retrieve PDF object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Zend_Pdf
     */
    protected function _getPdf()
    {
        if (!$this->_pdf instanceof \Zend_Pdf) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please define the PDF object before using.'));
        }

        return $this->_pdf;
    }

    /**
     * Create new page and assign to PDF object
     *
     * @param  array $settings
     * @return \Zend_Pdf_Page
     */
    public function newPage(array $settings = [])
    {
        $pageSize = !empty($settings['page_size']) ? $settings['page_size'] : \Zend_Pdf_Page::SIZE_A4;
        $page = $this->_getPdf()->newPage($pageSize);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;

        return $page;
    }

    /**
     * Draw lines
     *
     * Draw items array format:
     * lines        array;array of line blocks (required)
     * shift        int; full line height (optional)
     * height       int;line spacing (default 10)
     *
     * line block has line columns array
     *
     * column array format
     * text         string|array; draw text (required)
     * feed         int; x position (required)
     * font         string; font style, optional: bold, italic, regular
     * font_file    string; path to font file (optional for use your custom font)
     * font_size    int; font size (default 7)
     * align        string; text align (also see feed parametr), optional left, right
     * height       int;line spacing (default 10)
     *
     * @param  \Zend_Pdf_Page $page
     * @param  array $draw
     * @param  array $pageSettings
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Zend_Pdf_Page
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function drawLineBlocks(\Zend_Pdf_Page $page, array $draw, array $pageSettings = [])
    {
        foreach ($draw as $itemsProp) {
            if (!isset($itemsProp['lines']) || !is_array($itemsProp['lines'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('We don\'t recognize the draw line data. Please define the "lines" array.')
                );
            }
            $lines = $itemsProp['lines'];
            $height = isset($itemsProp['height']) ? $itemsProp['height'] : 10;

            if (empty($itemsProp['shift'])) {
                $shift = 0;
                foreach ($lines as $line) {
                    $maxHeight = 0;
                    foreach ($line as $column) {
                        $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                        if (!is_array($column['text'])) {
                            $column['text'] = [$column['text']];
                        }
                        $top = 0;
                        foreach ($column['text'] as $part) {
                            $top += $lineSpacing;
                        }

                        $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                    }
                    $shift += $maxHeight;
                }
                $itemsProp['shift'] = $shift;
            }

            if ($this->y - $itemsProp['shift'] < 15) {
                $page = $this->newPage($pageSettings);
            }

            foreach ($lines as $line) {
                $maxHeight = 0;
                foreach ($line as $column) {
                    $fontSize = empty($column['font_size']) ? 10 : $column['font_size'];
                    if (!empty($column['font_file'])) {
                        $font = \Zend_Pdf_Font::fontWithPath($column['font_file']);
                        $page->setFont($font, $fontSize);
                    } else {
                        $fontStyle = empty($column['font']) ? 'regular' : $column['font'];
                        switch ($fontStyle) {
                            case 'bold':
                                $font = $this->_setFontBold($page, $fontSize);
                                break;
                            case 'italic':
                                $font = $this->_setFontItalic($page, $fontSize);
                                break;
                            default:
                                $font = $this->_setFontRegular($page, $fontSize);
                                break;
                        }
                    }

                    if (!is_array($column['text'])) {
                        $column['text'] = [$column['text']];
                    }

                    $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                    $top = 0;
                    foreach ($column['text'] as $part) {
                        if ($this->y - $lineSpacing < 15) {
                            $page = $this->newPage($pageSettings);
                        }

                        $feed = $column['feed'];
                        $textAlign = empty($column['align']) ? 'left' : $column['align'];
                        $width = empty($column['width']) ? 0 : $column['width'];
                        switch ($textAlign) {
                            case 'right':
                                if ($width) {
                                    $feed = $this->getAlignRight($part, $feed, $width, $font, $fontSize);
                                } else {
                                    $feed = $feed - $this->widthForStringUsingFontSize($part, $font, $fontSize);
                                }
                                break;
                            case 'center':
                                if ($width) {
                                    $feed = $this->getAlignCenter($part, $feed, $width, $font, $fontSize);
                                }
                                break;
                            default:
                                break;
                        }
                        $page->drawText($part, $feed, $this->y - $top, 'UTF-8');
                        $top += $lineSpacing;
                    }

                    $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                }
                $this->y -= $maxHeight;
            }
        }

        return $page;
    }
}
