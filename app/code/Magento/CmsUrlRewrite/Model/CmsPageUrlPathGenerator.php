<?php
/**
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
