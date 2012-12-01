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
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales Order Shipment PDF model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Pdf_Shipment_Packaging extends Mage_Sales_Model_Order_Pdf_Abstract
{
    /**
     * Format pdf file
     *
     * @param  null $shipment
     * @return Zend_Pdf
     */
    public function getPdf($shipment = null)
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $page = $this->newPage();

        if ($shipment->getStoreId()) {
            Mage::app()->getLocale()->emulate($shipment->getStoreId());
            Mage::app()->setCurrentStore($shipment->getStoreId());
        }

        $this->_setFontRegular($page);
        $this->_drawHeaderBlock($page);

        $this->y = 740;
        $this->_drawPackageBlock($page);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_afterGetPdf();

        if ($shipment->getStoreId()) {
            Mage::app()->getLocale()->revert();
        }
        return $pdf;
    }

    /**
     * Draw header block
     *
     * @param  Zend_Pdf_Page $page
     * @return Mage_Sales_Model_Order_Pdf_Shipment_Packaging
     */
    protected function _drawHeaderBlock(Zend_Pdf_Page $page) {
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, 790, 570, 755);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $page->drawText(Mage::helper('Mage_Sales_Helper_Data')->__('Packages'), 35, 770, 'UTF-8');
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));

        return $this;
    }

    /**
     * Draw packages block
     *
     * @param  Zend_Pdf_Page $page
     * @return Mage_Sales_Model_Order_Pdf_Shipment_Packaging
     */
    protected function _drawPackageBlock(Zend_Pdf_Page $page)
    {
        if ($this->getPackageShippingBlock()) {
            $packaging = $this->getPackageShippingBlock();
        } else {
            $packaging = Mage::getBlockSingleton('Mage_Adminhtml_Block_Sales_Order_Shipment_Packaging');
        }
        $packages = $packaging->getPackages();

        $packageNum = 1;
        foreach ($packages as $packageId => $package) {
            $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->drawRectangle(25, $this->y + 15, 190, $this->y - 35);
            $page->drawRectangle(190, $this->y + 15, 350, $this->y - 35);
            $page->drawRectangle(350, $this->y + 15, 570, $this->y - 35);

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
            $page->drawRectangle(520, $this->y + 15, 570, $this->y - 5);

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $packageText = Mage::helper('Mage_Sales_Helper_Data')->__('Package') . ' ' . $packageNum;
            $page->drawText($packageText, 525, $this->y , 'UTF-8');
            $packageNum++;

            $package = new Varien_Object($package);
            $params = new Varien_Object($package->getParams());
            $dimensionUnits = Mage::helper('Mage_Usa_Helper_Data')->getMeasureDimensionName($params->getDimensionUnits());

            $typeText = Mage::helper('Mage_Sales_Helper_Data')->__('Type') . ' : '
                . $packaging->getContainerTypeByCode($params->getContainer());
            $page->drawText($typeText, 35, $this->y , 'UTF-8');

            if ($params->getLength() != null) {
                $lengthText = $params->getLength() .' '. $dimensionUnits;
            } else {
                $lengthText = '--';
            }
            $lengthText = Mage::helper('Mage_Sales_Helper_Data')->__('Length') . ' : ' . $lengthText;
            $page->drawText($lengthText, 200, $this->y , 'UTF-8');

            if ($params->getDeliveryConfirmation() != null) {
                $confirmationText = Mage::helper('Mage_Sales_Helper_Data')->__('Signature Confirmation')
                    . ' : '
                    . $packaging->getDeliveryConfirmationTypeByCode($params->getDeliveryConfirmation());
                $page->drawText($confirmationText, 355, $this->y , 'UTF-8');
            }

            $this->y = $this->y - 10;

            if ($packaging->displayCustomsValue() != null) {
                $customsValueText = Mage::helper('Mage_Sales_Helper_Data')->__('Customs Value')
                    . ' : '
                    . $packaging->displayPrice($params->getCustomsValue());
                $page->drawText($customsValueText, 35, $this->y , 'UTF-8');
            }
            if ($params->getWidth() != null) {
                $widthText = $params->getWidth() .' '. $dimensionUnits;
            } else {
                $widthText = '--';
            }
            $widthText = Mage::helper('Mage_Sales_Helper_Data')->__('Width') . ' : ' . $widthText;
            $page->drawText($widthText, 200, $this->y , 'UTF-8');

            if ($params->getContentType() != null) {
                if ($params->getContentType() == 'OTHER') {
                    $contentsValue = $params->getContentTypeOther();
                } else {
                    $contentsValue = $packaging->getContentTypeByCode($params->getContentType());
                }
                $contentsText = Mage::helper('Mage_Sales_Helper_Data')->__('Contents')
                    . ' : '
                    . $contentsValue;
                $page->drawText($contentsText, 355, $this->y , 'UTF-8');
            }

            $this->y = $this->y - 10;

            $weightText = Mage::helper('Mage_Sales_Helper_Data')->__('Total Weight') . ' : ' . $params->getWeight() .' '
                . Mage::helper('Mage_Usa_Helper_Data')->getMeasureWeightName($params->getWeightUnits());
            $page->drawText($weightText, 35, $this->y , 'UTF-8');

            if ($params->getHeight() != null) {
                $heightText = $params->getHeight() .' '. $dimensionUnits;
            } else {
                $heightText = '--';
            }
            $heightText = Mage::helper('Mage_Sales_Helper_Data')->__('Height') . ' : ' . $heightText;
            $page->drawText($heightText, 200, $this->y , 'UTF-8');

            $this->y = $this->y - 10;

            if ($params->getSize()) {
                $sizeText = Mage::helper('Mage_Sales_Helper_Data')->__('Size') . ' : ' . ucfirst(strtolower($params->getSize()));
                $page->drawText($sizeText, 35, $this->y , 'UTF-8');
            }
            if ($params->getGirth() != null) {
                $dimensionGirthUnits = Mage::helper('Mage_Usa_Helper_Data')->getMeasureDimensionName($params->getGirthDimensionUnits());
                $girthText = Mage::helper('Mage_Sales_Helper_Data')->__('Girth')
                             . ' : ' . $params->getGirth() . ' ' . $dimensionGirthUnits;
                $page->drawText($girthText, 200, $this->y , 'UTF-8');
            }

            $this->y = $this->y - 5;
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
            $page->drawRectangle(25, $this->y, 570, $this->y - 30 - (count($package->getItems()) * 12));

            $this->y = $this->y - 10;
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $page->drawText(Mage::helper('Mage_Sales_Helper_Data')->__('Items in the Package'), 30, $this->y, 'UTF-8');

            $txtIndent = 5;
            $itemCollsNumber = $packaging->displayCustomsValue() ? 5 : 4;
            $itemCollsX[0] = 30; //  coordinate for Product name
            $itemCollsX[1] = 250; // coordinate for Product name
            $itemCollsXEnd = 565;
            $itemCollsXStep = round(($itemCollsXEnd - $itemCollsX[1]) / ($itemCollsNumber - 1));
            // calculate coordinates for all other cells (Weight, Customs Value, Qty Ordered, Qty)
            for ($i = 2; $i <= $itemCollsNumber; $i++) {
                $itemCollsX[$i] = $itemCollsX[$i-1] + $itemCollsXStep;
            }

            $i = 0;
            $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->drawRectangle($itemCollsX[$i], $this->y - 5, $itemCollsX[++$i], $this->y - 15);
            $page->drawRectangle($itemCollsX[$i], $this->y - 5, $itemCollsX[++$i], $this->y - 15);
            $page->drawRectangle($itemCollsX[$i], $this->y - 5, $itemCollsX[++$i], $this->y - 15);
            $page->drawRectangle($itemCollsX[$i], $this->y - 5, $itemCollsX[++$i], $this->y - 15);
            $page->drawRectangle($itemCollsX[$i], $this->y - 5, $itemCollsXEnd, $this->y - 15);

            $this->y = $this->y - 12;
            $i = 0;

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $page->drawText(Mage::helper('Mage_Sales_Helper_Data')->__('Product'), $itemCollsX[$i] + $txtIndent, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('Mage_Sales_Helper_Data')->__('Weight'), $itemCollsX[++$i] + $txtIndent, $this->y, 'UTF-8');
            if ($packaging->displayCustomsValue()) {
                $page->drawText(
                    Mage::helper('Mage_Sales_Helper_Data')->__('Customs Value'),
                    $itemCollsX[++$i] + $txtIndent,
                    $this->y,
                    'UTF-8'
                );
            }
            $page->drawText(
                Mage::helper('Mage_Sales_Helper_Data')->__('Qty Ordered'), $itemCollsX[++$i] + $txtIndent, $this->y, 'UTF-8'
            );
            $page->drawText(Mage::helper('Mage_Sales_Helper_Data')->__('Qty'), $itemCollsX[++$i] + $txtIndent, $this->y, 'UTF-8');

            $i = 0;
            foreach ($package->getItems() as $itemId => $item) {
                $item = new Varien_Object($item);
                $i = 0;

                $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
                $page->drawRectangle($itemCollsX[$i], $this->y - 3, $itemCollsX[++$i], $this->y - 15);
                $page->drawRectangle($itemCollsX[$i], $this->y - 3, $itemCollsX[++$i], $this->y - 15);
                $page->drawRectangle($itemCollsX[$i], $this->y - 3, $itemCollsX[++$i], $this->y - 15);
                $page->drawRectangle($itemCollsX[$i], $this->y - 3, $itemCollsX[++$i], $this->y - 15);
                $page->drawRectangle($itemCollsX[$i], $this->y - 3, $itemCollsXEnd, $this->y - 15);

                $this->y = $this->y - 12;
                $i = 0;
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                $page->drawText($item->getName(), $itemCollsX[$i] + $txtIndent, $this->y, 'UTF-8');
                $page->drawText($item->getWeight(), $itemCollsX[++$i] + $txtIndent, $this->y, 'UTF-8');
                if ($packaging->displayCustomsValue()) {
                    $page->drawText(
                        $packaging->displayPrice($item->getCustomsValue()),
                        $itemCollsX[++$i] + $txtIndent,
                        $this->y,
                        'UTF-8'
                    );
                }
                $page->drawText(
                    $packaging->getQtyOrderedItem($item->getOrderItemId()),
                    $itemCollsX[++$i] + $txtIndent,
                    $this->y,
                    'UTF-8'
                );
                $page->drawText($item->getQty()*1, $itemCollsX[++$i] + $txtIndent, $this->y, 'UTF-8');
            }
            $this->y = $this->y - 30;
        }
        return $this;
    }
}
