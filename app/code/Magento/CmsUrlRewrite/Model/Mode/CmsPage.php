<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CmsUrlRewrite\Model\Mode;

use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\UrlRewrite\Model\Mode\ModeInterface;
use Magento\UrlRewrite\Model\UrlRewrite;


class CmsPage implements ModeInterface
{
    const ENTITY_TYPE = 'cms_page';
    const SORT_ORDER = 30;
    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $pageFactory;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /**
     * @var \Magento\Cms\Model\Page|null
     */
    protected $cmsPage;

    /**
     * @param PageFactory $pageFactory
     * @param RequestInterface $request
     */
    public function __construct(
        PageFactory $pageFactory,
        RequestInterface $request
    )
    {
        $this->pageFactory = $pageFactory;
        $this->request = $request;
    }
    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getLabel()
    {
        return __('For CMS Page');
    }

    /**
     * @return string
     */
    public function getEntityType()
    {
        return self::ENTITY_TYPE;
    }

    /**
     * @return string
     */
    public function getEditBlockClass()
    {
        return 'Magento\CmsUrlRewrite\Block\Page\Edit';
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return self::SORT_ORDER;
    }

    /**
     * @param UrlRewrite $urlRewrite
     * @return \Magento\Cms\Model\Page
     */
    public function getCmsPage(UrlRewrite $urlRewrite)
    {
        if (is_null($this->cmsPage)) {
            $this->cmsPage = $this->pageFactory->create();
            $pageId = (int)$this->request->getParam($this->getEntityType(), 0);
            if (!$pageId && $urlRewrite->getId() && $urlRewrite->getEntityType() === $this->getEntityType()) {
                $pageId = $urlRewrite->getEntityId();
            }
            if ($pageId) {
                $this->cmsPage->load($pageId);
            }
        }
        return $this->cmsPage;
    }

    /**
     * @param UrlRewrite $urlRewrite
     * @return bool|mixed
     */
    public function match(UrlRewrite $urlRewrite)
    {
        return $this->getCmsPage($urlRewrite)->getId() || $this->request->has($this->getEntityType());
    }
}
