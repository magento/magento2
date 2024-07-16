<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rss\Controller\Feed;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NotFoundException;

class Index extends \Magento\Rss\Controller\Feed implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * Index action
     *
     * @return void
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->scopeConfig->getValue('rss/config/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            throw new NotFoundException(__('Page not found.'));
        }

        $type = $this->getRequest()->getParam('type');
        try {
            $provider = $this->rssManager->getProvider($type);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundException(__($e->getMessage()));
        }

        if ($provider->isAuthRequired() && !$this->auth()) {
            return;
        }

        if (!$provider->isAllowed()) {
            throw new NotFoundException(__('Page not found.'));
        }

        /** @var $rss \Magento\Rss\Model\Rss */
        $rss = $this->rssFactory->create();
        $rss->setDataProvider($provider);

        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
        $tags = ['rss'];
        if ($provider instanceof IdentityInterface) {
            $tags = array_merge($tags, $provider->getIdentities());
        }
        $this->getResponse()->setHeader('X-Magento-Tags', implode(',', $tags));
        $this->getResponse()->setBody($rss->createRssXml());
    }
}
