<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Ui\ContentType;

use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\FileSystem;
use Magento\Framework\View\TemplateEnginePool;
use Magento\Framework\Xml\Generator;

/**
 * Class Xml
 */
class Xml implements ContentTypeInterface
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
     * @var \Magento\Framework\Xml\Generator
     */
    protected $generator;

    /**
     * @param FileSystem $filesystem
     * @param TemplateEnginePool $templateEnginePool
     * @param Generator $generator
     */
    public function __construct(FileSystem $filesystem, TemplateEnginePool $templateEnginePool, Generator $generator)
    {
        $this->filesystem = $filesystem;
        $this->templateEnginePool = $templateEnginePool;
        $this->generator = $generator;
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
            $result = $this->getDataXml($view);
        }
        return $result;
    }

    /**
     * @param UiComponentInterface $view
     * @return string
     */
    protected function getDataXml(UiComponentInterface $view)
    {
        $result = [
            'configuration' => $view->getRenderContext()->getStorage()->getComponentsData($view->getName())->getData(),
            'data' => []
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
     * @param \Magento\Framework\Object $object
     * @return string
     */
    protected function objectToXml(\Magento\Framework\Object $object)
    {
        return (string)$object;
    }
}
