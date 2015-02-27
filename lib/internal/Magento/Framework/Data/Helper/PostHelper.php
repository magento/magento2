<?php
/**
 * Helper to obtain post data for postData widget
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Helper;

use Magento\Framework\App\Helper\Context;

class PostHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Url\Helper
     */
    private $urlHelper;

    public function __construct(
        Context $context,
        \Magento\Framework\Url\Helper $urlHelper
    ) {
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
        if (!isset($data[\Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED])) {
            $data[\Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED] = $this->urlHelper->getEncodedUrl();
        }
        return json_encode(['action' => $url, 'data' => $data]);
    }
}
