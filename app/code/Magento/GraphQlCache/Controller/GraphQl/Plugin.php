<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\GraphQl;

use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\GraphQlCache\Model\CacheTags;

class Plugin
{
    /**
     * @var CacheTags
     */
    private $cacheTags;

    /**
     * @param CacheTags $cacheTags
     */
    public function __construct(CacheTags $cacheTags)
    {
        $this->cacheTags = $cacheTags;
    }

    public function afterDispatch(
        FrontControllerInterface $subject,
        ResponseInterface $response,
        RequestInterface $request
    ) {
        /** @var \Magento\Framework\App\Request\Http $request */
        /** @var \Magento\Framework\Webapi\Response $response */
        $cacheTags = $this->cacheTags->getCacheTags();
        if ($request->isGet() && count($cacheTags)) {
            // assume that response should be cacheable if it contains cache tags
            $response->setHeader('Pragma', 'cache', true);
            // TODO: Take from configuration
            $response->setHeader('Cache-Control', 'max-age=86400, public, s-maxage=86400', true);
            $response->setHeader('X-Magento-Tags', implode(',', $cacheTags), true);
        }
        return $response;
    }
}
