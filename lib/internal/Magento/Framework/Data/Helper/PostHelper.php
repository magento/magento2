<?php
/**
 * Helper to obtain post data for postData widget
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Url\Helper\Data as UrlHelper;

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
     * get data for post by javascript in format acceptable to $.mage.dataPost widget
     *
     * @param string $url
     * @param array $data
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
