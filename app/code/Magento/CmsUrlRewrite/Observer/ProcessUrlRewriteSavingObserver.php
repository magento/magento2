<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CmsUrlRewrite\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class \Magento\CmsUrlRewrite\Observer\ProcessUrlRewriteSavingObserver
 *
 * @since 2.0.0
 */
class ProcessUrlRewriteSavingObserver implements ObserverInterface
{
    /**
     * @var \Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator
     * @since 2.0.0
     */
    protected $cmsPageUrlRewriteGenerator;

    /**
     * @var UrlPersistInterface
     * @since 2.0.0
     */
    protected $urlPersist;

    /**
     * @param \Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator $cmsPageUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     * @since 2.0.0
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
     * @since 2.0.0
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
