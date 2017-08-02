<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\ContentType;

use Magento\Framework\Json\Encoder;
use Magento\Framework\View\FileSystem;
use Magento\Framework\View\TemplateEnginePool;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Layout\Generator\Structure;

/**
 * Class Json
 * @since 2.0.0
 */
class Json extends AbstractContentType
{
    /**
     * Generator structure instance
     *
     * @var Structure
     * @since 2.0.0
     */
    private $structure;

    /**
     * Encoder
     *
     * @var Encoder
     * @since 2.0.0
     */
    private $encoder;

    /**
     * Constructor
     *
     * @param FileSystem $filesystem
     * @param TemplateEnginePool $templateEnginePool
     * @param Encoder $encoder
     * @param Structure $structure
     * @since 2.0.0
     */
    public function __construct(
        FileSystem $filesystem,
        TemplateEnginePool $templateEnginePool,
        Encoder $encoder,
        Structure $structure
    ) {
        parent::__construct($filesystem, $templateEnginePool);
        $this->encoder = $encoder;
        $this->structure = $structure;
    }

    /**
     * Render data
     *
     * @param UiComponentInterface $component
     * @param string $template
     * @return string
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function render(UiComponentInterface $component, $template = '')
    {
        $context = $component->getContext();
        $isComponent = $context->getRequestParam('componentJson');
        if ($isComponent) {
            $data = $this->structure->generate($component);
            return $this->encoder->encode($data);
        } else {
            $data = $component->getContext()->getDataSourceData($component);
            $data = reset($data);
            return $this->encoder->encode(
                isset($data['config']['data']) ? $data['config']['data'] : []
            );
        }
    }
}
