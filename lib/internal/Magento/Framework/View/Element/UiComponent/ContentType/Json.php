<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
 */
class Json extends AbstractContentType
{
    /**
     * Generator structure instance
     *
     * @var Structure
     */
    private $structure;

    /**
     * Encoder
     *
     * @var Encoder
     */
    private $encoder;

    /**
     * Constructor
     *
     * @param FileSystem $filesystem
     * @param TemplateEnginePool $templateEnginePool
     * @param Encoder $encoder
     * @param Structure $structure
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
