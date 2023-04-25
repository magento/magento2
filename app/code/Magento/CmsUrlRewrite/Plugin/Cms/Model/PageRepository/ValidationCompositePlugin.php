<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsUrlRewrite\Plugin\Cms\Model\PageRepository;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\PageRepository\ValidationComposite;
use Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator;

/**
 * Generate url_key if the merchant didn't fill this field
 */
class ValidationCompositePlugin
{
    /**
     * @var CmsPageUrlPathGenerator
     */
    private $cmsPageUrlPathGenerator;

    /**
     * @param CmsPageUrlPathGenerator $cmsPageUrlPathGenerator
     */
    public function __construct(
        CmsPageUrlPathGenerator $cmsPageUrlPathGenerator
    ) {
        $this->cmsPageUrlPathGenerator = $cmsPageUrlPathGenerator;
    }

    /**
     * Before save handler
     *
     * @param ValidationComposite $subject
     * @param PageInterface $page
     */
    public function beforeSave(
        ValidationComposite $subject,
        PageInterface $page
    ) {
        $urlKey = $page->getData('identifier');
        if ($urlKey === '' || $urlKey === null) {
            $page->setData('identifier', $this->cmsPageUrlPathGenerator->generateUrlKey($page));
        }
    }
}
