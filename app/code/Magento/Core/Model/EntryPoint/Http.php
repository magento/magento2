<?php
/**
 * Http entry point
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\EntryPoint;

class Http extends \Magento\Core\Model\AbstractEntryPoint
{
    /**
     * Process http request, output html page or proper information about an exception (if any)
     */
    public function processRequest()
    {
        try {
            parent::processRequest();
        } catch (\Magento\Core\Model\Session\Exception $e) {
            header(
                'Location: ' . $this->_objectManager->get('Magento\Core\Model\StoreManager')->getStore()->getBaseUrl()
            );
        } catch (\Magento\Core\Model\Store\Exception $e) {
            require $this->_objectManager->get('Magento\Core\Model\Dir')
                    ->getDir(\Magento\Core\Model\Dir::PUB) . DS . 'errors' . DS . '404.php';
        } catch (\Magento\BootstrapException $e) {
            header('Content-Type: text/plain', true, 503);
            echo $e->getMessage();
        } catch (\Exception $e) {
            $this->processException($e);
        }
    }

    /**
     * Run http application
     */
    protected function _processRequest()
    {
        $request = $this->_objectManager->get('Magento\Core\Controller\Request\Http');
        $response = $this->_objectManager->get('Magento\Core\Controller\Response\Http');
        $handler = $this->_objectManager->get('Magento\HTTP\Handler\Composite');
        $handler->handle($request, $response);
    }
}
