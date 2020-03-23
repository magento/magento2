<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Csp\Api\InlineUtilInterface;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\TemplateEngine\Php;

/**
 * Plugin that adds CSP utility to templates context.
 */
class TemplateRenderingPlugin
{
    /**
     * @var InlineUtilInterface
     */
    private $util;

    /**
     * @param InlineUtilInterface $util
     */
    public function __construct(InlineUtilInterface $util)
    {
        $this->util = $util;
    }

    /**
     * Add $csp variable to a template scope.
     *
     * @param Php $renderer
     * @param BlockInterface $block
     * @param string $fileName
     * @param array $dictionary
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeRender(Php $renderer, BlockInterface $block, $fileName, array $dictionary): array
    {
        $dictionary['csp'] = $this->util;

        return [$block, $fileName, $dictionary];
    }
}
