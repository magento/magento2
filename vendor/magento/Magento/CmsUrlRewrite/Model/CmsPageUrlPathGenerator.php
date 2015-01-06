<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CmsUrlRewrite\Model;

class CmsPageUrlPathGenerator
{
    /** @var \Magento\Framework\Filter\FilterManager */
    protected $filterManager;

    public function __construct(
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {
        $this->filterManager = $filterManager;
    }

    /**
     * @param \Magento\Cms\Model\Page $cmsPage
     *
     * @return string
     */
    public function getUrlPath($cmsPage)
    {
        return $cmsPage->getIdentifier();
    }

    /**
     * Get canonical product url path
     *
     * @param \Magento\Cms\Model\Page $cmsPage
     * @return string
     */
    public function getCanonicalUrlPath($cmsPage)
    {
        return 'cms/page/view/page_id/' . $cmsPage->getId();
    }

    /**
     * Generate CMS page url key based on url_key entered by merchant or page title
     *
     * @param \Magento\Cms\Model\Page $cmsPage
     * @return string
     */
    public function generateUrlKey($cmsPage)
    {
        $urlKey = $cmsPage->getIdentifier();
        return $this->filterManager->translitUrl($urlKey === '' || $urlKey === null ? $cmsPage->getTitle() : $urlKey);
    }
}
