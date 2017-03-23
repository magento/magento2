<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CmsUrlRewrite\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class ProcessUrlRewriteSavingObserver implements ObserverInterface
{
    /**
     * @var \Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator
     */
    protected $cmsPageUrlRewriteGenerator;

    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @param \Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator $cmsPageUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     */
    public function __construct(CmsPageUrlRewriteGenerator $cmsPageUrlRewriteGenerator, UrlPersistInterface $urlPersist)
    {
        $this->cmsPageUrlRewriteGenerator = $cmsPageUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
    }

    /**
     * Generate urls for UrlRewrite and save it in storage
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var $cmsPage \Magento\Cms\Model\Page */
        $cmsPage = $observer->getEvent()->getObject();

        if ($cmsPage->dataHasChangedFor('identifier') || $cmsPage->dataHasChangedFor('store_id')) {
            $urls = $this->cmsPageUrlRewriteGenerator->generate($cmsPage);

            $this->urlPersist->deleteByData([
                UrlRewrite::ENTITY_ID => $cmsPage->getId(),
                UrlRewrite::ENTITY_TYPE => CmsPageUrlRewriteGenerator::ENTITY_TYPE,
            ]);
            $this->urlPersist->replace($urls);
        }
    }
}
