<?php
/**
 * Phrase (for replacing Data Value with Object)
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

use Magento\Framework\Phrase\Renderer\Placeholder as RendererPlaceholder;
use Magento\Framework\Phrase\RendererInterface;
use Zend\Stdlib\JsonSerializable;

/**
 * @api
 * @since 2.0.0
 */
class Phrase implements JsonSerializable
{
    /**
     * Default phrase renderer. Allows stacking renderers that "don't know about each other"
     *
     * @var RendererInterface
     * @since 2.0.0
     */
    private static $renderer;

    /**
     * String for rendering
     *
     * @var string
     * @since 2.0.0
     */
    private $text;

    /**
     * Arguments for placeholder values
     *
     * @var array
     * @since 2.0.0
     */
    private $arguments;

    /**
     * Set default Phrase renderer
     *
     * @param RendererInterface $renderer
     * @return void
     * @since 2.0.0
     */
    public static function setRenderer(RendererInterface $renderer)
    {
        self::$renderer = $renderer;
    }

    /**
     * Get default Phrase renderer
     *
     * @return RendererInterface
     * @since 2.0.0
     */
    public static function getRenderer()
    {
        if (!self::$renderer) {
            self::$renderer = new RendererPlaceholder();
        }
        return self::$renderer;
    }

    /**
     * Phrase construct
     *
     * @param string $text
     * @param array $arguments
     * @since 2.0.0
     */
    public function __construct($text, array $arguments = [])
    {
        $this->text = (string)$text;
        $this->arguments = $arguments;
    }

    /**
     * Get phrase base text
     *
     * @return string
     * @since 2.0.0
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Get phrase message arguments
     *
     * @return array
     * @since 2.0.0
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Render phrase
     *
     * @return string
     * @since 2.0.0
     */
    public function render()
    {
        try {
            return self::getRenderer()->render([$this->text], $this->getArguments());
        } catch (\Exception $e) {
            return $this->getText();
        }
    }

    /**
     * Defers rendering to the last possible moment (when converted to string)
     *
     * @return string
     * @since 2.0.0
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return string
     * @since 2.0.0
     */
    public function jsonSerialize()
    {
        return $this->render();
    }
}
