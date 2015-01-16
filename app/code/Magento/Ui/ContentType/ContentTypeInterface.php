<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\ContentType;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface ContentTypeInterface
 */
interface ContentTypeInterface
{
    /**
     * Render data
     *
     * @param UiComponentInterface $view
     * @param string $template
     * @return mixed
     */
    public function render(UiComponentInterface $view, $template = '');
}
