<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

/**
 * @api
 */
class RendererList extends AbstractBlock
{
    /**
     * Renderer templates cache
     *
     * @var array
     */
    protected $rendererTemplates = [];

    /**
     * Retrieve renderer by code
     *
     * @param string $type
     * @param string $default
     * @param string $rendererTemplate
     * @return bool|AbstractBlock
     * @throws \RuntimeException
     */
    public function getRenderer($type, $default = null, $rendererTemplate = null)
    {
        /** @var \Magento\Framework\View\Element\Template $renderer */
        $renderer = $this->getChildBlock($type) ?: $this->getChildBlock($default);
        if (!$renderer instanceof BlockInterface) {
            throw new \RuntimeException('Renderer for type "' . $type . '" does not exist.');
        }
        $renderer->setRenderedBlock($this);

        if (!isset($this->rendererTemplates[$type])) {
            $this->rendererTemplates[$type] = $renderer->getTemplate();
        } else {
            $renderer->setTemplate($this->rendererTemplates[$type]);
        }

        if ($rendererTemplate) {
            $renderer->setTemplate($rendererTemplate);
        }
        return $renderer;
    }
}
