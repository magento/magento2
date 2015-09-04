<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Fallback\Rule;

/**
 * Rule for module
 */
class Module implements RuleInterface
{
    /**
     * Optional params for rule
     *
     * @var array
     */
    protected $optionalParams;

    /**
     * Pattern for a simple rule
     *
     * @var string
     */
    protected $pattern;

    /**
     * Module's namespace
     *
     * @var string
     */
    protected $namespace;

    /**
     * Module name
     *
     * @var string
     */
    protected $module;

    /**
     * Constructor
     *
     * @param string $pattern
     * @param string $namespace
     * @param string $module
     * @param array $optionalParams
     */
    public function __construct($pattern, $namespace, $module, array $optionalParams = [])
    {
        $this->pattern = $pattern;
        $this->namespace = $namespace;
        $this->module = $module;
        $this->optionalParams = $optionalParams;
    }

    /**
     * Get ordered list of folders to search for a file
     *
     * @param array $params array of parameters
     * @return array folders to perform a search
     * @throws \InvalidArgumentException
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
        if (isset($params['namespace'])
            && $params['namespace'] == $this->namespace
            && isset($params['module'])
            && $params['module'] == $this->module
        ) {
            return [$pattern];
        }
        return [];
    }
}
