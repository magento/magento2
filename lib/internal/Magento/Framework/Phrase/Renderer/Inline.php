<?php
/**
 * Translate Inline Phrase renderer
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Phrase\Renderer;

class Inline implements \Magento\Framework\Phrase\RendererInterface
{
    /**
     * @var \Magento\Framework\TranslateInterface
     */
    protected $translator;

    /**
     * @var \Magento\Framework\Translate\Inline\ProviderInterface
     */
    protected $inlineProvider;

    /**
     * @param \Magento\Framework\TranslateInterface $translator
     * @param \Magento\Framework\Translate\Inline\ProviderInterface $inlineProvider
     */
    public function __construct(
        \Magento\Framework\TranslateInterface $translator,
        \Magento\Framework\Translate\Inline\ProviderInterface $inlineProvider
    ) {
        $this->translator = $translator;
        $this->inlineProvider = $inlineProvider;
    }

    /**
     * Render source text
     *
     * @param [] $source
     * @param [] $arguments
     * @return string
     */
    public function render(array $source, array $arguments)
    {
        $text = end($source);

        if (!$this->inlineProvider->get()->isAllowed()) {
            return $text;
        }

        if (strpos($text, '{{{') === false
            || strpos($text, '}}}') === false
            || strpos($text, '}}{{') === false
        ) {
            $text = '{{{'
                . implode('}}{{', array_reverse($source))
                . '}}{{' . $this->translator->getTheme() . '}}}';
        }

        return $text;
    }
}
