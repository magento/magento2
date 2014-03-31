<?php
/**
 * PageCache controller
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\PageCache\Controller;

class Block extends \Magento\App\Action\Action
{
    /**
     * Returns block content depends on ajax request
     *
     * @return void
     */
    public function renderAction()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_forward('noroute');
            return;
        }

        $blocks = $this->_getBlocks();
        $data = array();
        foreach ($blocks as $blockName => $blockInstance) {
            $data[$blockName] = $blockInstance->toHtml();
        }

        $this->getResponse()->setPrivateHeaders(\Magento\PageCache\Helper\Data::PRIVATE_MAX_AGE_CACHE);
        $this->getResponse()->appendBody(json_encode($data));
    }

    /**
     * Returns block content as part of ESI request from Varnish
     *
     * @return void
     */
    public function esiAction()
    {
        $response = $this->getResponse();
        $blocks = $this->_getBlocks();
        $html = '';
        $ttl = 0;

        if (!empty($blocks)) {
            $blockInstance = array_shift($blocks);
            $html = $blockInstance->toHtml();
            $ttl = $blockInstance->getTtl();
            if ($blockInstance instanceof \Magento\View\Block\IdentityInterface) {
                $response->setHeader('X-Magento-Tags', implode(',', $blockInstance->getIdentities()));
            }
        }
        $response->appendBody($html);
        $response->setPublicHeaders($ttl);
    }

    /**
     * Get blocks from layout by handles
     *
     * @return array [\Element\BlockInterface]
     */
    protected function _getBlocks()
    {
        $blocks = $this->getRequest()->getParam('blocks', '');
        $handles = $this->getRequest()->getParam('handles', '');

        if (!$handles || !$blocks) {
            return array();
        }
        $blocks = json_decode($blocks);
        $handles = json_decode($handles);

        $this->_view->loadLayout($handles);
        $data = array();

        $layout = $this->_view->getLayout();
        foreach ($blocks as $blockName) {
            $blockInstance = $layout->getBlock($blockName);
            if (is_object($blockInstance)) {
                $data[$blockName] = $blockInstance;
            }
        }

        return $data;
    }
}
