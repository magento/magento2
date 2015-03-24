<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\TemplateEngine;

use Magento\Ui\TemplateEngine\Xhtml\Result;
use Magento\Ui\TemplateEngine\Xhtml\Template;
use Magento\Ui\TemplateEngine\Xhtml\Compiler;
use Magento\Ui\TemplateEngine\Xhtml\ResultFactory;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\TemplateEngineInterface;
use Magento\Ui\TemplateEngine\Xhtml\CompilerFactory;
use Magento\Ui\TemplateEngine\Xhtml\TemplateFactory;
use Magento\Framework\View\Element\UiComponent\Config\Provider\Template as TemplateProvider;

/**
 * Class Xhtml
 */
class Xhtml implements TemplateEngineInterface
{
    const INSTANCE_NAME = 'Magento\Ui\Content\Template\Type\Xhtml\Template';

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
     * @param \Magento\Framework\View\Element\BlockInterface $block
     * @param string $templateFile
     * @param array $dictionary
     * @return Result
     */
    public function render(BlockInterface $block, $templateFile, array $dictionary = [])
    {
        /** @var Template $template */
        $template = $this->templateFactory->create(['content' => $this->templateProvider->getTemplate($templateFile)]);

        /** @var Result $result */
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
