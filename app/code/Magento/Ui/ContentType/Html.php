<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Ui\ContentType;

use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\FileSystem;
use Magento\Framework\View\TemplateEnginePool;

/**
 * Class Html
 */
class Html implements ContentTypeInterface
{
    /**
     * @var \Magento\Framework\View\FileSystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\View\TemplateEnginePool
     */
    protected $templateEnginePool;

    /**
     * Constructor
     *
     * @param FileSystem $filesystem
     * @param TemplateEnginePool $templateEnginePool
     */
    public function __construct(FileSystem $filesystem, TemplateEnginePool $templateEnginePool)
    {
        $this->filesystem = $filesystem;
        $this->templateEnginePool = $templateEnginePool;
    }

    /**
     * Render data
     *
     * @param UiComponentInterface $view
     * @param string $template
     * @return string
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
            $result = '';
        }
        return $result;
    }
}
