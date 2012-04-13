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
 * @package     Mage_Api2
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Webservice API2 renderer factory model
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Api2_Model_Renderer
{
    /**
     * Get Renderer of given type
     *
     * @param array|string $acceptTypes
     * @throws Mage_Api2_Exception
     * @throws Exception
     * @return Mage_Api2_Model_Renderer_Interface
     */
    public static function factory($acceptTypes)
    {
        /** @var $helper Mage_Api2_Helper_Data */
        $helper   = Mage::helper('Mage_Api2_Helper_Data');
        $adapters = $helper->getResponseRenderAdapters();

        if (!is_array($acceptTypes)) {
            $acceptTypes = array($acceptTypes);
        }

        $type = null;
        $adapterPath = null;
        foreach ($acceptTypes as $type) {
            foreach ($adapters as $item) {
                $itemType = $item->type;
                if ($type == $itemType
                    || $type == current(explode('/', $itemType)) . '/*' || $type == '*/*'
                ) {
                    $adapterPath = $item->model;
                    break 2;
                }
            }
        }

        //if server can't respond in any of accepted types it SHOULD send 406(not acceptable)
        if (null === $adapterPath) {
            throw new Mage_Api2_Exception(
                'Server can not understand Accept HTTP header media type.',
                Mage_Api2_Model_Server::HTTP_NOT_ACCEPTABLE
            );
        }

        $adapter = Mage::getModel($adapterPath);
        if (!$adapter) {
            throw new Exception(sprintf('Response renderer adapter for content type "%s" not found.', $type));
        }

        return $adapter;
    }
}
