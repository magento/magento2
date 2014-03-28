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
 * @category    Magento
 * @package     Magento_Cms
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Cms\Model\Page;

/**
 * Page Url rewrite model
 *
 * @method \Magento\Cms\Model\Resource\Page\Urlrewrite getResource() getResource()
 * @method int getCmsPageId() getCmsPageId()
 * @method int getUrlRewriteId() getUrlRewriteId()
 * @method \Magento\Cms\Model\Page\Urlrewrite setCmsPageId() setCmsPageId(int)
 * @method \Magento\Cms\Model\Page\Urlrewrite setUrlRewriteId() setUrlRewriteId(int)
 */
class Urlrewrite extends \Magento\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Cms\Model\Resource\Page\Urlrewrite');
    }

    /**
     * Generate id path
     *
     * @param \Magento\Cms\Model\Page $cmsPage
     * @return string
     */
    public function generateIdPath($cmsPage)
    {
        return 'cms_page/' . $cmsPage->getId();
    }

    /**
     * Generate target path
     *
     * @param \Magento\Cms\Model\Page $cmsPage
     * @return string
     */
    public function generateTargetPath($cmsPage)
    {
        return 'cms/page/view/page_id/' . $cmsPage->getId();
    }

    /**
     * Get request path
     *
     * @param \Magento\Cms\Model\Page $cmsPage
     * @return string
     */
    public function generateRequestPath($cmsPage)
    {
        return $cmsPage->getIdentifier();
    }
}
