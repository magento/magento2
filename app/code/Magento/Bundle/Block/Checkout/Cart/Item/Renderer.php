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
 * @package     Magento_Bundle
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Bundle\Block\Checkout\Cart\Item;

use Magento\Bundle\Helper\Catalog\Product\Configuration;

/**
 * Shopping cart item render block
 *
 * @category    Magento
 * @package     Magento_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Renderer extends \Magento\Checkout\Block\Cart\Item\Renderer
{
    /**
     * @var Configuration
     */
    protected $_configurationHelper = null;

    /**
     * Bundle catalog product configuration
     *
     * @var Configuration
     */
    protected $_bundleProdConfigur = null;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Product\Configuration $productConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Core\Helper\Url $urlHelper
     * @param \Magento\Message\ManagerInterface $messageManager
     * @param Configuration $bundleProdConfigur
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Product\Configuration $productConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Core\Helper\Url $urlHelper,
        \Magento\Message\ManagerInterface $messageManager,
        Configuration $bundleProdConfigur,
        array $data = array()
    ) {
        $this->_bundleProdConfigur = $bundleProdConfigur;
        parent::__construct(
            $context,
            $productConfig,
            $checkoutSession,
            $imageHelper,
            $urlHelper,
            $messageManager,
            $data
        );
        $this->_isScopePrivate = true;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_configurationHelper = $this->_bundleProdConfigur;
    }

    /**
     * Get bundled selections (slections-products collection)
     *
     * Returns array of options objects.
     * Each option object will contain array of selections objects
     *
     * @param bool $useCache
     * @return array
     */
    protected function _getBundleOptions($useCache = true)
    {
        return $this->_configurationHelper->getBundleOptions($this->getItem());
    }

    /**
     * Obtain final price of selection in a bundle product
     *
     * @param \Magento\Catalog\Model\Product $selectionProduct
     * @return float
     */
    protected function _getSelectionFinalPrice($selectionProduct)
    {
        $helper = $this->_bundleProdConfigur;
        $result = $helper->getSelectionFinalPrice($this->getItem(), $selectionProduct);
        return $result;
    }

    /**
     * Get selection quantity
     *
     * @param int $selectionId
     * @return float
     */
    protected function _getSelectionQty($selectionId)
    {
        return $this->_configurationHelper->getSelectionQty($this->getProduct(), $selectionId);
    }

    /**
     * Overloaded method for getting list of bundle options
     * Caches result in quote item, because it can be used in cart 'recent view' and on same page in cart checkout
     *
     * @return array
     */
    public function getOptionList()
    {
        return $this->_configurationHelper->getOptions($this->getItem());
    }

    /**
     * Return cart item error messages
     *
     * @return array
     */
    public function getMessages()
    {
        $messages = array();
        $quoteItem = $this->getItem();

        // Add basic messages occuring during this page load
        $baseMessages = $quoteItem->getMessage(false);
        if ($baseMessages) {
            foreach ($baseMessages as $message) {
                $messages[] = array('text' => $message, 'type' => $quoteItem->getHasError() ? 'error' : 'notice');
            }
        }

        return $messages;
    }
}
