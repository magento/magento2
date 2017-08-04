<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\ContentType;

use Magento\Framework\Xml\Generator;
use Magento\Framework\View\FileSystem;
use Magento\Framework\View\TemplateEnginePool;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class Xml
 */
class Xml extends AbstractContentType
{
    /**
     * @var FileSystem
     */
    protected $filesystem;

    /**
     * @var TemplateEnginePool
     */
    protected $templateEnginePool;

    /**
     * @var Generator
     */
    protected $generator;

    /**
     * Constructor
     *
     * @param FileSystem $filesystem
     * @param TemplateEnginePool $templateEnginePool
     * @param Generator $generator
     */
    public function __construct(
        FileSystem $filesystem,
        TemplateEnginePool $templateEnginePool,
        Generator $generator
    ) {
        $this->generator = $generator;
        parent::__construct($filesystem, $templateEnginePool);
    }

    /**
     * Render data
     *
     * @param UiComponentInterface $view
     * @param string $template
     * @return string
     * @throws \Exception
     */
    public function render(UiComponentInterface $view, $template = '')
    {
        $templateEngine = false;
        if ($template) {
            $extension = pathinfo($template, PATHINFO_EXTENSION);
            $templateEngine = $this->templateEnginePool->get($extension);
        }
        if ($templateEngine) {
            $path = $this->filesystem->getTemplateFileName($template);
            $result = $templateEngine->render($view, $path);
        } else {
            $result = $this->getDataXml($view);
        }

        throw new \Exception('Please implement XML renderer');
    }

    /**
     * @param UiComponentInterface $view
     * @return string
     */
    protected function getDataXml(UiComponentInterface $view)
    {
        $result = [
            'configuration' => $view->getRenderContext()->getStorage()->getComponentsData($view->getName())->getData(),
            'data' => [],
        ];
        foreach ($view->getRenderContext()->getStorage()->getData($view->getName()) as $key => $value) {
            if (is_object($value)) {
                if (method_exists($value, 'toXml')) {
                    $result['data'][$key] = $value->toXml();
                } else {
                    $result['data'][$key] = $this->objectToXml($value);
                }
            } else {
                $result['data'][$key] = $value;
            }
        }
        return $this->generator->arrayToXml($result);
    }

    /**
     * Convert object to xml format
     *
     * @param \Magento\Framework\DataObject $object
     * @return string
     */
    protected function objectToXml(\Magento\Framework\DataObject $object)
    {
        return (string)$object;
    }
}
