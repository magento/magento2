<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine;

use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\TemplateEngineInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\Template;
use Magento\Framework\View\TemplateEngine\Xhtml\ResultFactory;
use Magento\Framework\View\TemplateEngine\Xhtml\ResultInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerFactory;
use Magento\Framework\View\TemplateEngine\Xhtml\TemplateFactory;
use Magento\Framework\View\Element\UiComponent\Config\Provider\Template as TemplateProvider;

/**
 * Class Xhtml
 */
class Xhtml implements TemplateEngineInterface
{
    /**
     * @var TemplateProvider
     */
    protected $templateProvider;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var CompilerFactory
     */
    protected $compilerFactory;

    /**
     * Constructor
     *
     * @param TemplateProvider $templateProvider
     * @param ResultFactory $resultFactory
     * @param TemplateFactory $templateFactory
     * @param CompilerFactory $compilerFactory
     */
    public function __construct(
        TemplateProvider $templateProvider,
        ResultFactory $resultFactory,
        TemplateFactory $templateFactory,
        CompilerFactory $compilerFactory
    ) {
        $this->templateProvider = $templateProvider;
        $this->resultFactory = $resultFactory;
        $this->templateFactory = $templateFactory;
        $this->compilerFactory = $compilerFactory;
    }

    /**
     * Render template
     *
     * Render the named template in the context of a particular block and with
     * the data provided in $vars.
     *
     * @param BlockInterface $block
     * @param string $templateFile
     * @param array $dictionary
     * @return ResultInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function render(BlockInterface $block, $templateFile, array $dictionary = [])
    {
        /** @var Template $template */
        $template = $this->templateFactory->create(['content' => $this->templateProvider->getTemplate($templateFile)]);

        $result = $this->resultFactory->create(
            [
                'template' => $template,
                'compiler' => $this->compilerFactory->create(),
                'component' => $block
            ]
        );

        return $result;
    }
}
