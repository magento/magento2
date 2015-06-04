<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Model\Mode;

use Magento\Framework\App\RequestInterface;
use Magento\UrlRewrite\Model\Mode\ModeInterface;
use Magento\UrlRewrite\Model\UrlRewrite;

class Custom implements ModeInterface
{
    const ENTITY_TYPE = 'custom';
    const SORT_ORDER = 1;

    protected $request;

    public function __construct(
        RequestInterface $request
    )
    {
        $this->request = $request;
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getLabel()
    {
        return __('Custom');
    }

    public function getEntityType()
    {
        return self::ENTITY_TYPE;
    }

    /**
     * @return string
     */
    public function getEditBlockClass()
    {
        return 'Magento\UrlRewrite\Block\Edit';
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return self::SORT_ORDER;
    }

    public function match(UrlRewrite $urlRewrite)
    {
        return $this->request->has('id');
    }
}
