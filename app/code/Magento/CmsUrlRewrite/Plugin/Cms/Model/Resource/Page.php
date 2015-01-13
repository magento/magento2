<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CmsUrlRewrite\Plugin\Cms\Model\Resource;

/**
 * Before save plugin for \Magento\Cms\Model\Resource\Page:
 * - autogenerates url_key if the merchant didn't fill this field
 */
class Page
{
    /** @var \Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator */
    protected $cmsPageUrlPathGenerator;

    public function __construct(\Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator $cmsPageUrlPathGenerator)
    {
        $this->cmsPageUrlPathGenerator = $cmsPageUrlPathGenerator;
    }

    /**
     * Before save handler
     *
     * @param \Magento\Cms\Model\Resource\Page $subject
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Cms\Model\Resource\Page $subject,
        \Magento\Framework\Model\AbstractModel $object
    ) {
        /** @var $object \Magento\Cms\Model\Page */
        $urlKey = $object->getData('identifier');
        if ($urlKey === '' || $urlKey === null) {
            $object->setData('identifier', $this->cmsPageUrlPathGenerator->generateUrlKey($object));
        }
    }
}
