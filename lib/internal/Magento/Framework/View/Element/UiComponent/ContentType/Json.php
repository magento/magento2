<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\ContentType;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class Json
 */
class Json extends AbstractContentType
{
    /**
     * Render data
     *
     * @param UiComponentInterface $component
     * @param string $template
     * @return string
     * @throws \Exception
     */
    public function render(UiComponentInterface $component, $template = '')
    {
        return json_encode(['error' => 'TODO fix me']);
    }
}
