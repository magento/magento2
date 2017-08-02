<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Fallback\Rule;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Module\Dir\Reader;

/**
 * Class with simple substitution parameters to values
 * @since 2.0.0
 */
class Simple implements RuleInterface
{
    /**
     * Optional params for rule
     *
     * @var array
     * @since 2.0.0
     */
    protected $optionalParams;

    /**
     * Pattern for a simple rule
     *
     * @var string
     * @since 2.0.0
     */
    protected $pattern;

    /**
     * Constructor
     *
     * @param string $pattern
     * @param array $optionalParams
     * @since 2.0.0
     */
    public function __construct(
        $pattern,
        array $optionalParams = []
    ) {
        $this->pattern = $pattern;
        $this->optionalParams = $optionalParams;
    }

    /**
     * Get ordered list of folders to search for a file
     *
     * @param array $params array of parameters
     * @return array folders to perform a search
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function getPatternDirs(array $params)
    {
        $pattern = $this->pattern;
        if (preg_match_all('/<([a-zA-Z\_]+)>/', $pattern, $matches)) {
            foreach ($matches[1] as $placeholder) {
                if (empty($params[$placeholder])) {
                    if (in_array($placeholder, $this->optionalParams)) {
                        return [];
                    } else {
                        throw new \InvalidArgumentException("Required parameter '{$placeholder}' was not passed");
                    }
                }
                $pattern = str_replace('<' . $placeholder . '>', $params[$placeholder], $pattern);
            }
        }
        return [$pattern];
    }
}
