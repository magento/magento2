<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\TemplateEngine;

use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\TemplateEngineInterface;

/**
 * Template engine that enables PHP templates to be used for rendering
 */
class Php implements TemplateEngineInterface
{
    /**
     * @var BlockInterface
     */
    protected $_currentBlock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_helperFactory;

    /**
     * @var object[]
     */
    private $blockVariables = [];

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $helperFactory
     * @param object[] $blockVariables
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $helperFactory,
        array $blockVariables = []
    ) {
        $this->_helperFactory = $helperFactory;
        $this->blockVariables = $blockVariables;
    }

    /**
     * Render output
     *
     * Include the named PHTML template using the given block as the $this
     * reference, though only public methods will be accessible.
     *
     * @param BlockInterface $block
     * @param string $fileName
     * @param array $dictionary
     * @return string
     * @throws \Throwable
     */
    public function render(BlockInterface $block, $fileName, array $dictionary = [])
    {
        ob_start();
        try {
            $tmpBlock = $this->_currentBlock;
            $this->_currentBlock = $block;
            $dictionary = array_merge($this->blockVariables, $dictionary);
            extract($dictionary, EXTR_SKIP);
            include $fileName;
            $this->_currentBlock = $tmpBlock;
        } catch (\Throwable $exception) {
            ob_end_clean();
            throw $exception;
        }
        /** Get output buffer. */
        $output = ob_get_clean();
        return $output;
    }

    /**
     * Redirects methods calls to the current block
     *
     * This is needed because the templates are included in the context of this engine
     * rather than in the context of the block.
     *
     * @param   string $method
     * @param   array  $args
     * @return  mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->_currentBlock, $method], $args);
    }

    /**
     * Redirects isset calls to the current block
     *
     * This is needed because the templates are included in the context of this engine rather than
     * in the context of the block.
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->_currentBlock->{$name});
    }

    /**
     * Allows read access to properties of the current block
     *
     * This is needed because the templates are included in the context of this engine rather
     * than in the context of the block.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_currentBlock->{$name};
    }

    /**
     * Get helper singleton
     *
     * @param string $className
     * @return \Magento\Framework\App\Helper\AbstractHelper
     * @throws \LogicException
     */
    public function helper($className)
    {
        $helper = $this->_helperFactory->get($className);
        if (false === $helper instanceof \Magento\Framework\App\Helper\AbstractHelper) {
            throw new \LogicException($className . ' doesn\'t extends Magento\Framework\App\Helper\AbstractHelper');
        }

        return $helper;
    }
}
