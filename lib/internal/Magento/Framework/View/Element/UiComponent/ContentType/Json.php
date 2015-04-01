<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\ContentType;

use Magento\Framework\View\FileSystem;
use Magento\Framework\View\TemplateEnginePool;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class Json
 */
class Json extends AbstractContentType
{
    /**
     * Constructor
     *
     * @param FileSystem $filesystem
     * @param TemplateEnginePool $templateEnginePool
     */
    public function __construct(
        FileSystem $filesystem,
        TemplateEnginePool $templateEnginePool
    ) {
        parent::__construct($filesystem, $templateEnginePool);
    }

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
        $data = $component->getDataSourceData();
        $data = reset($data);

        return json_encode($data['config']['data']);
    }
}
