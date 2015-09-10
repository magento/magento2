<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Fallback\Rule;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Module\Dir\Reader;

/**
 * Class with simple substitution parameters to values
 */
class Simple implements RuleInterface
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
     * Component registry
     *
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * Module directory reader
     *
     * @var Reader
     */
    protected $moduleDirReader;

    /**
     * Constructor
     *
     * @param string $pattern
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param Reader $moduleDirReader
     * @param array $optionalParams
     */
    public function __construct(
        $pattern,
        ComponentRegistrarInterface $componentRegistrar,
        Reader $moduleDirReader,
        array $optionalParams = []
    ) {
        $this->pattern = $pattern;
        $this->optionalParams = $optionalParams;
        $this->componentRegistrar = $componentRegistrar;
        $this->moduleDirReader = $moduleDirReader;
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
        if (strpos($pattern, '<module_dir>/<namespace>/<module>') !== false) {
            $path = $this->moduleDirReader->getModuleDir('', $params['namespace'] . '_' . $params['module']);
            $pattern = str_replace('<module_dir>/<namespace>/<module>', $path, $pattern);
        }
        if (strpos($pattern, '<theme_dir>/<area>/<theme_path>') !== false) {
            $path = $this->componentRegistrar->getPath(
                ComponentRegistrar::THEME,
                $params['area'] . '/' . $params['theme_path']
            );
            $pattern = str_replace('<theme_dir>/<area>/<theme_path>', $path, $pattern);
        }
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
