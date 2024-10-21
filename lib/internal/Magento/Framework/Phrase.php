<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase\Renderer\Placeholder as RendererPlaceholder;
use Magento\Framework\Phrase\RendererInterface;

/**
 * Phrase (for replacing Data Value with Object)
 *
 * @api
 * @since 100.0.2
 */
class Phrase implements \JsonSerializable
{
    /**
     * Default phrase renderer. Allows stacking renderers that "don't know about each other"
     *
     * @var RendererInterface
     */
    private static $renderer;

    /**
     * String for rendering
     *
     * @var string
     */
    private $text;

    /**
     * Arguments for placeholder values
     *
     * @var array
     */
    private $arguments;

    /**
     * Set default Phrase renderer
     *
     * @param RendererInterface $renderer
     * @return void
     */
    public static function setRenderer(RendererInterface $renderer)
    {
        self::$renderer = $renderer;
    }

    /**
     * Get default Phrase renderer
     *
     * @return RendererInterface
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
     * @throws LocalizedException
     */
    public function __construct($text, array $arguments = [])
    {
        $this->text = (string)$text;
        $this->arguments = $arguments;

        if ($this->text === '') {
            $objectManager = ObjectManager::getInstance();
            $appState = $objectManager->get(State::class);

            if ($appState->getMode() === State::MODE_DEVELOPER) {
                throw new LocalizedException(new self('Unable to translate empty string'));
            }
        }
    }

    /**
     * Get phrase base text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Get phrase message arguments
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Render phrase
     *
     * @return string
     */
    public function render()
    {
        try {
            return self::getRenderer()->render([$this->text], $this->getArguments());
        } catch (\Throwable $e) {
            return $this->getText();
        }
    }

    /**
     * Defers rendering to the last possible moment (when converted to string)
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->render();
    }
}
