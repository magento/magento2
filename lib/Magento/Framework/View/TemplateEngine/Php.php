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
namespace Magento\Framework\View\TemplateEngine;

use Magento\Framework\View\TemplateEngineInterface;
use Magento\Framework\View\Element\BlockInterface;

/**
 * Template engine that enables PHP templates to be used for rendering
 */
class Php implements TemplateEngineInterface
{
    /**
     * Current block
     *
     * @var BlockInterface
     */
    protected $_currentBlock;

    /**
     * Helper factory
     *
     * @var \Magento\Framework\ObjectManager
     */
    protected $_helperFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManager $helperFactory
     */
    public function __construct(\Magento\Framework\ObjectManager $helperFactory)
    {
        $this->_helperFactory = $helperFactory;
    }

    /**
     * Render output
     *
     * Include the named PHTML template using the given block as the $this
     * reference, though only public methods will be accessible.
     *
     * @param BlockInterface           $block
     * @param string                   $fileName
     * @param array                    $dictionary
     * @return string
     * @throws \Exception
     */
    public function render(BlockInterface $block, $fileName, array $dictionary = array())
    {
        ob_start();
        try {
            $tmpBlock = $this->_currentBlock;
            $this->_currentBlock = $block;
            extract($dictionary, EXTR_SKIP);
            include $fileName;
            $this->_currentBlock = $tmpBlock;
        } catch (\Exception $exception) {
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
        return call_user_func_array(array($this->_currentBlock, $method), $args);
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
