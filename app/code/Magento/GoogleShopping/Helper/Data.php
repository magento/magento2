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
namespace Magento\GoogleShopping\Helper;

/**
 * Google Content Data Helper
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\String
     */
    protected $string;

    /**
     * Store manager
     *
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Stdlib\String $string
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\String $string,
        \Magento\Framework\StoreManagerInterface $storeManager
    ) {
        $this->string = $string;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Get Google Content Product ID
     *
     * @param int $productId
     * @param int $storeId
     * @return string
     */
    public function buildContentProductId($productId, $storeId)
    {
        return $productId . '_' . $storeId;
    }

    /**
     * Remove characters and words not allowed by Google Content in title and content (description).
     *
     * To avoid "Expected response code 200, got 400.
     * Reason: There is a problem with the character encoding of this attribute"
     *
     * @param string $string
     * @return string
     */
    public function cleanAtomAttribute($string)
    {
        return $this->string->substr(preg_replace('/[\pC¢€•—™°½]|shipping/ui', '', $string), 0, 3500);
    }

    /**
     * Normalize attribute's name.
     * The name has to be in lower case and the words are separated by symbol "_".
     * For instance: Meta Description = meta_description
     *
     * @param string $name
     * @return string
     */
    public function normalizeName($name)
    {
        return strtolower(preg_replace('/[\s_]+/', '_', $name));
    }

    /**
     * Parse \Exception Response Body
     *
     * @param string $message \Exception message to parse
     * @param null|\Magento\Catalog\Model\Product $product
     * @return string
     */
    public function parseGdataExceptionMessage($message, $product = null)
    {
        $result = array();
        foreach (explode("\n", $message) as $row) {
            if (trim($row) == '') {
                continue;
            }

            if (strip_tags($row) == $row) {
                $row = preg_replace('/@ (.*)/', __('See \'\1\''), $row);
                if (!is_null($product)) {
                    $row .= ' ' . __(
                        "for product '%1' (in '%2' store)",
                        $product->getName(),
                        $this->_storeManager->getStore($product->getStoreId())->getName()
                    );
                }
                $result[] = $row;
                continue;
            }

            // parse not well-formatted xml
            preg_match_all('/(reason|field|type)=\"([^\"]+)\"/', $row, $matches);

            if (is_array($matches) && count($matches) == 3) {
                if (is_array($matches[1]) && count($matches[1]) > 0) {
                    $c = count($matches[1]);
                    for ($i = 0; $i < $c; $i++) {
                        if (isset($matches[2][$i])) {
                            $result[] = ucfirst($matches[1][$i]) . ': ' . $matches[2][$i];
                        }
                    }
                }
            }
        }
        return implode(". ", $result);
    }
}
