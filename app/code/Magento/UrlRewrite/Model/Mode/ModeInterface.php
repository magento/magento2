<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Model\Mode;

use Magento\UrlRewrite\Model\UrlRewrite;

interface ModeInterface
{
    /**
     * @return string|\Magento\Framework\Phrase
     */
    public function getLabel();

    /**
     * @return string
     */
    public function getEntityType();

    /**
     * @return string
     */
    public function getEditBlockClass();

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * @param UrlRewrite $urlRewrite
     * @return mixed
     */
    public function match(UrlRewrite $urlRewrite);
}
