<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlPlayground\Plugin\App;

use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;

/**
 * Class AreaList
 * After Plugin to change graphql Area for rendering playground
 */
class AreaList
{

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Should render playground
     *
     * @return bool
     */
    private function shouldRenderPlayground(): bool
    {
        $shouldRenderPlayground = false;
        if (!$this->request->getParam('query') && $this->request->isGet()) {
            $shouldRenderPlayground = true;
        }
        return $shouldRenderPlayground;
    }

    public function afterGetCodeByFrontName(
        \Magento\Framework\App\AreaList $subject,
        $result,
        $frontName
    ) {
        $graphqlFrontName = $subject->getFrontName(Area::AREA_GRAPHQL);
        if ($frontName != $graphqlFrontName || $this->shouldRenderPlayground() == false) {
            return $result;
        }
        return Area::AREA_FRONTEND;
    }
}
