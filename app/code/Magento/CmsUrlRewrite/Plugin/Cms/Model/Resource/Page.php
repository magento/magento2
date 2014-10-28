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
