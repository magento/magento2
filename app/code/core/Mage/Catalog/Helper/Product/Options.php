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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog Product Custom Options helper
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Helper_Product_Options extends Mage_Core_Helper_Abstract
{
    /**
     * Fetches and outputs file to user browser
     * $info is array with following indexes:
     *  - 'path' - full file path
     *  - 'type' - mime type of file
     *  - 'size' - size of file
     *  - 'title' - user-friendly name of file (usually - original name as uploaded in Magento)
     *
     * @param Mage_Core_Controller_Response_Http $response
     * @param string $filePath
     * @param array $info
     * @return bool
     */
    public function downloadFileOption($response, $filePath, $info)
    {
        try {
            $response->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Content-type', $info['type'], true)
                ->setHeader('Content-Length', $info['size'])
                ->setHeader('Content-Disposition', 'inline' . '; filename='.$info['title'])
                ->clearBody();
            $response->sendHeaders();

            readfile($filePath);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
}
