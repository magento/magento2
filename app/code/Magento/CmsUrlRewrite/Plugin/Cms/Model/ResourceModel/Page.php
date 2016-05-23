<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CmsUrlRewrite\Plugin\Cms\Model\ResourceModel;

use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator;
use Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Before save and around delete plugin for \Magento\Cms\Model\ResourceModel\Page:
 * - autogenerates url_key if the merchant didn't fill this field
 * - remove all url rewrites for cms page on delete
 */
class Page
{
    /**
     * @var \Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator
     */
    protected $cmsPageUrlPathGenerator;

    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @param CmsPageUrlPathGenerator $cmsPageUrlPathGenerator
     * @param UrlPersistInterface $urlPersist
     */
    public function __construct(
        CmsPageUrlPathGenerator $cmsPageUrlPathGenerator,
        UrlPersistInterface $urlPersist
    ) {
        $this->cmsPageUrlPathGenerator = $cmsPageUrlPathGenerator;
        $this->urlPersist = $urlPersist;
    }

    /**
     * Before save handler
     *
     * @param \Magento\Cms\Model\ResourceModel\Page $subject
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Cms\Model\ResourceModel\Page $subject,
        \Magento\Framework\Model\AbstractModel $object
    ) {
        /** @var $object \Magento\Cms\Model\Page */
        $urlKey = $object->getData('identifier');
        if ($urlKey === '' || $urlKey === null) {
            $object->setData('identifier', $this->cmsPageUrlPathGenerator->generateUrlKey($object));
        }
    }

    /**
     * On delete handler to remove related url rewrites
     *
     * @param \Magento\Cms\Model\ResourceModel\Page $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $page
     * @return \Magento\Cms\Model\ResourceModel\Page
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDelete(
        \Magento\Cms\Model\ResourceModel\Page $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $page
    ) {
        $result = $proceed($page);
        if ($page->isDeleted()) {
            $this->urlPersist->deleteByData(
                [
                    UrlRewrite::ENTITY_ID => $page->getId(),
                    UrlRewrite::ENTITY_TYPE => CmsPageUrlRewriteGenerator::ENTITY_TYPE,
                ]
            );
        }

        return $result;
    }
}
