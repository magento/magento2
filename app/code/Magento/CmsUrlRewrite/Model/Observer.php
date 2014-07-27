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

use Magento\Framework\Event\Observer as EventObserver;
use Magento\CmsUrlRewrite\Service\V1\CmsPageUrlGeneratorInterface;
use Magento\UrlRedirect\Service\V1\UrlSaveInterface;
use Magento\Framework\Model\Exception;

class Observer
{
    /**
     * @var CmsPageUrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var \Magento\UrlRedirect\Service\V1\UrlSaveInterface
     */
    protected $urlSave;

    /**
     * @param CmsPageUrlGeneratorInterface $urlGenerator
     * @param UrlSaveInterface $urlSave
     */
    public function __construct(CmsPageUrlGeneratorInterface $urlGenerator, UrlSaveInterface $urlSave)
    {
        $this->urlGenerator = $urlGenerator;
        $this->urlSave = $urlSave;
    }

    /**
     * Generate urls for UrlRewrite and save it in storage
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws Exception|\Exception
     */
    public function processUrlRewriteSaving(EventObserver $observer)
    {
        /** @var $cmsPage \Magento\Cms\Model\Page */
        $cmsPage = $observer->getEvent()->getObject();
        if ($cmsPage->getOrigData('identifier') !== $cmsPage->getData('identifier')) {
            $urls = $this->urlGenerator->generate($cmsPage);
            try {
                $this->urlSave->save($urls);
            } catch (\Exception $e) {
                if ($e->getCode() === 23000) { // Integrity constraint violation: 1062 Duplicate entry
                    throw new Exception(__('A page URL key for specified store already exists.'));
                }
                throw $e;
            }
        }
    }
}
