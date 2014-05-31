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

namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\View\Asset;
use Magento\Framework\View\Url\CssResolver;

/**
 * Support of notation "Module_Name::file/path.ext" in CSS-files
 *
 * This implementation is specific to CSS, despite that the actual algorithm of calculating offsets is generic.
 * The part specific to CSS is the fact that a CSS file may refer to another file and the relative path has to be
 * based off the current location of CSS-file. So context of base path can be known ONLY at the moment
 * of traversing the CSS contents in context of the file location.
 * Other than that, the algorithm of resolving notation "Module_Name::file/path.ext" is generic
 */
class ModuleNotation implements Asset\PreProcessorInterface
{
    /**
     * @var \Magento\Framework\View\Url\CssResolver
     */
    private $cssResolver;

    /**
     * @var \Magento\Framework\View\Asset\ModuleNotation\Resolver
     */
    private $notationResolver;

    /**
     * @param CssResolver $cssResolver
     * @param \Magento\Framework\View\Asset\ModuleNotation\Resolver $notationResolver
     */
    public function __construct(
        CssResolver $cssResolver,
        \Magento\Framework\View\Asset\ModuleNotation\Resolver $notationResolver
    ) {
        $this->cssResolver = $cssResolver;
        $this->notationResolver = $notationResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Chain $chain)
    {
        $asset = $chain->getAsset();
        $callback = function ($path) use ($asset) {
            return $this->notationResolver->convertModuleNotationToPath($asset, $path);
        };
        $chain->setContent($this->cssResolver->replaceRelativeUrls($chain->getContent(), $callback));
    }
}
