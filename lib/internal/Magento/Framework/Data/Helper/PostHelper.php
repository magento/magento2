<?php

/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Data\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Url\Helper\Data as UrlHelper;

/**
 * Helper to obtain post data for postData widget
 */
class PostHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @param Context $context
     * @param UrlHelper $urlHelper
     */
    public function __construct(
        Context $context,
        UrlHelper $urlHelper
    ) {
        parent::__construct($context);
        $this->urlHelper = $urlHelper;
    }

    /**
     * Get data for post by javascript in format acceptable to $.mage.dataPost widget
     *
     * @param string $url
     * @param array $data
     *
     * @return string
     */
    public function getPostData($url, array $data = [])
    {
        if (!isset($data[\Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED])) {
            $data[\Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED] = $this->urlHelper->getEncodedUrl();
        }

        return json_encode(['action' => $url, 'data' => $data]);
    }
}
