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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @method setImageType(string)
 * @method string getImageType()
 * @method setImageWidth(string)
 * @method string getImageWidth()
 * @method setImageHeight(string)
 * @method string getImageHeight()
 * @method setImageLabel(string)
 * @method string getImageLabel()
 * @method setAddWhiteBorders(bool)
 * @method bool getAddWhiteBorders()
 * @method Mage_Catalog_Helper_Image getImageHelper()
 * @method setImageHelper(Mage_Catalog_Helper_Image $imageHelper)
 * @method Mage_Catalog_Model_Product getProduct()
 *
 * Product image block
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_Catalog_Block_Product_Image extends Mage_Core_Block_Template
{
    /**
     * Template image only
     *
     * @var string
     */
    protected $_templateImage = 'Mage_Catalog::product/image.phtml';

    /**
     * Template image with html frame border
     *
     * @var string
     */
    protected $_templateWithBorders = 'Mage_Catalog::product/image_with_borders.phtml';

    /**
     * @var Mage_Catalog_Model_Product_Image_View
     */
    protected $_productImageView;

    /**
     * Constructor
     * @param Mage_Core_Block_Template_Context $context
     * @param Mage_Catalog_Model_Product_Image_View $productImageView
     * @param array $data
     */
    public function __construct(
        Mage_Core_Block_Template_Context $context,
        Mage_Catalog_Model_Product_Image_View $productImageView,
        array $data = array()
    ) {
        $this->_productImageView = $productImageView;
        parent::__construct($context, $data);
    }

    /**
     * Initialize model
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $location
     * @param string $module
     * @return Mage_Catalog_Block_Product_Image
     */
    public function init(Mage_Catalog_Model_Product $product, $location, $module = 'Mage_Catalog')
    {
        $this->_productImageView->init($product, $location, $module);
        $this->_initTemplate();
        return $this;
    }

    /**
     * Select a template based on white_border flag
     *
     * @return Mage_Catalog_Block_Product_Image
     */
    protected function _initTemplate()
    {
        if (null === $this->getTemplate()) {
            $template = $this->getProductImageView()->isWhiteBorders()
                ? $this->_templateImage
                : $this->_templateWithBorders;
            $this->setTemplate($template);
        }
        return $this;
    }

    /**
     * Getter for product image view model
     *
     * @return Mage_Catalog_Model_Product_Image_View
     */
    public function getProductImageView()
    {
        return $this->_productImageView;
    }
}
