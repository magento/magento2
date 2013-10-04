<?php
/**
 * Template engine that enables PHP templates to be used for rendering
 *
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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\TemplateEngine;

class Php implements \Magento\Core\Model\TemplateEngine\EngineInterface
{
    /**
     * @var \Magento\Core\Block\Template
     */
    protected $_currentBlock;

    /**
     * Include the named PHTML template using the given block as the $this
     * reference, though only public methods will be accessible.
     *
     * @param \Magento\Core\Block\Template $block
     * @param string                   $fileName
     * @param array                    $dictionary
     *
     * @return string
     * @throws \Exception any exception that the template may throw
     */
    public function render(\Magento\Core\Block\Template $block, $fileName, array $dictionary = array())
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
     * Redirects methods calls to the current block.  This is needed because
     * the templates are included in the context of this engine rather than
     * in the context of the block.
     *
     * @param   string $method
     * @param   array  $args
     *
     * @return  mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->_currentBlock, $method), $args);
    }

    /**
     * Redirects isset calls to the current block.  This is needed because
     * the templates are included in the context of this engine rather than
     * in the context of the block.
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->_currentBlock->$name);
    }

    /**
     * Allows read access to properties of the current block.  This is needed
     * because the templates are included in the context of this engine rather
     * than in the context of the block.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_currentBlock->$name;
    }
}
