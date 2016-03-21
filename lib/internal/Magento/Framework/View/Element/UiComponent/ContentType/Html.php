<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\ContentType;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class Html
 */
class Html extends AbstractContentType
{
    /**
     * Render data
     *
     * @param UiComponentInterface $component
     * @param string $template
     * @return string
     */
    public function render(UiComponentInterface $component, $template = '')
    {
        $result = '';
        if ($template) {
            $extension = pathinfo($template, PATHINFO_EXTENSION);
            $templateEngine = $this->templateEnginePool->get($extension);
            $result = $templateEngine->render($component, $this->getTemplate($extension, $template));
        }
        return $result;
    }

    /**
     * Get template path
     *
     * @param string $extension
     * @param string $template
     * @return string
     */
    protected function getTemplate($extension, $template)
    {
        switch ($extension) {
            case 'xhtml':
                return $template;
            default:
                return $this->filesystem->getTemplateFileName($template);
        }
    }
}
