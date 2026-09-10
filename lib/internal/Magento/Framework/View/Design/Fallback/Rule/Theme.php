<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Design\Fallback\Rule;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;

/**
 * Fallback Rule Theme
 *
 * An aggregate of a fallback rule that propagates it to every theme according to a hierarchy
 */
class Theme implements RuleInterface
{
    /**
     * Underlying fallback rule
     *
     * @var RuleInterface
     */
    protected $rule;

    /**
     * Locates registered component paths
     *
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * Constructors
     *
     * @param RuleInterface $rule
     * @param ComponentRegistrarInterface $componentRegistrar
     */
    public function __construct(RuleInterface $rule, ComponentRegistrarInterface $componentRegistrar)
    {
        $this->rule = $rule;
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Propagate an underlying fallback rule to every theme in a hierarchy: parent, grandparent, etc.
     *
     * @param array $params
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getPatternDirs(array $params)
    {
        if (!array_key_exists('theme', $params) || !$params['theme'] instanceof ThemeInterface) {
            throw new \InvalidArgumentException(
                'Parameter "theme" should be specified and should implement the theme interface.'
            );
        }
        $result = [];
        /** @var $theme ThemeInterface */
        $theme = $params['theme'];
        unset($params['theme']);
        while ($theme) {
            if ($theme->getFullPath()) {
                $params['theme_dir'] = $this->componentRegistrar->getPath(
                    ComponentRegistrar::THEME,
                    $theme->getFullPath()
                );
                if (empty($params['theme_dir'])
                    && $theme instanceof \Magento\Framework\DataObject
                    && $theme->getData('area')
                    && $theme->getThemePath()
                ) {
                    // Area-emulated processes must locate physical themes registered under their own area
                    // (GitHub #17673).
                    $params['theme_dir'] = $this->componentRegistrar->getPath(
                        ComponentRegistrar::THEME,
                        $theme->getData('area') . ThemeInterface::PATH_SEPARATOR . $theme->getThemePath()
                    );
                }

                $params = $this->getThemePubStaticDir($theme, $params);
                $result = array_merge($result, $this->rule->getPatternDirs($params));
            }
            $theme = $theme->getParentTheme();
        }
        return $result;
    }

    /**
     * Get dir of Theme that contains published static view files
     *
     * @param ThemeInterface $theme
     * @param array $params
     * @return array
     */
    private function getThemePubStaticDir(ThemeInterface $theme, $params = [])
    {
        if (empty($params['theme_pubstatic_dir'])
            && isset($params['file'])
            && pathinfo($params['file'], PATHINFO_EXTENSION) === 'css'
        ) {
            $params['theme_pubstatic_dir'] = $this->getDirectoryList()
                    ->getPath(DirectoryList::STATIC_VIEW)
                . '/' . $theme->getArea() . '/' . $theme->getCode()
                . (isset($params['locale']) ? '/' . $params['locale'] : '');
        }

        return $params;
    }

    /**
     * Get DirectoryList instance
     *
     * Kept for backward compatibility.
     *
     * @return DirectoryList
     *
     * @deprecated 101.0.0 unused DI-bypassing accessor kept for BC
     * @see \Magento\Framework\App\Filesystem\DirectoryList
     */
    private function getDirectoryList()
    {
        if (null === $this->directoryList) {
            $this->directoryList = ObjectManager::getInstance()->get(DirectoryList::class);
        }

        return $this->directoryList;
    }
}
