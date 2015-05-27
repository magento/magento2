<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\ContentType;

use Magento\Framework\Json\Encoder;
use Magento\Framework\View\FileSystem;
use Magento\Framework\View\TemplateEnginePool;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class Json
 */
class Json extends AbstractContentType
{
    /**
     * @var Encoder
     */
    private $encoder;

    /**
     * Constructor
     *
     * @param FileSystem $filesystem
     * @param TemplateEnginePool $templateEnginePool
     * @param Encoder $encoder
     */
    public function __construct(
        FileSystem $filesystem,
        TemplateEnginePool $templateEnginePool,
        Encoder $encoder
    ) {
        parent::__construct($filesystem, $templateEnginePool);
        $this->encoder = $encoder;
    }

    /**
     * Render data
     *
     * @param UiComponentInterface $component
     * @param string $template
     * @return string
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function render(UiComponentInterface $component, $template = '')
    {
        $data = $component->getContext()->getDataSourceData($component);
        $data = reset($data);

        return $this->encoder->encode($data['config']['data']);
    }
}
