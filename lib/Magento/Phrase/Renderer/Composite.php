<?php
/**
 * Composite Phrase renderer
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Phrase\Renderer;

class Composite implements \Magento\Phrase\RendererInterface
{
    /**
     * Renderer factory
     *
     * @var \Magento\Phrase\Renderer\Factory
     */
    protected $_rendererFactory;

    /**
     * List of \Magento\Phrase\RendererInterface
     *
     * @var array
     */
    protected $_renderers = array();

    /**
     * Renderer construct
     *
     * @param \Magento\Phrase\Renderer\Factory $rendererFactory
     * @param array $renderers
     */
    public function __construct(
        \Magento\Phrase\Renderer\Factory $rendererFactory,
        array $renderers = array()
    ) {
        $this->_rendererFactory = $rendererFactory;

        foreach ($renderers as $render) {
            $this->_append($render);
        }
    }

    /**
     * Add renderer to the end of the chain
     *
     * @param string $render
     */
    protected function _append($render)
    {
        array_push($this->_renderers, $this->_rendererFactory->create($render));
    }

    /**
     * {@inheritdoc}
     */
    public function render($text, array $arguments = array())
    {
        /** @var \Magento\Phrase\Renderer\Composite $render */
        foreach ($this->_renderers as $render) {
            $text = $render->render($text, $arguments);
        }
        return $text;
    }
}
