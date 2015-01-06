<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\PageCache\Controller\Block;

class Esi extends \Magento\PageCache\Controller\Block
{
    /**
     * Returns block content as part of ESI request from Varnish
     *
     * @return void
     */
    public function execute()
    {
        $response = $this->getResponse();
        $blocks = $this->_getBlocks();
        $html = '';
        $ttl = 0;

        if (!empty($blocks)) {
            $blockInstance = array_shift($blocks);
            $html = $blockInstance->toHtml();
            $ttl = $blockInstance->getTtl();
            if ($blockInstance instanceof \Magento\Framework\View\Block\IdentityInterface) {
                $response->setHeader('X-Magento-Tags', implode(',', $blockInstance->getIdentities()));
            }
        }
        $response->appendBody($html);
        $response->setPublicHeaders($ttl);
    }
}
