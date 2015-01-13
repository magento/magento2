<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Fallback\Rule;

use Magento\Framework\View\Design\ThemeInterface;

/**
 * Fallback Rule Theme
 *
 * An aggregate of a fallback rule that propagates it to every theme according to a hierarchy
 */
class Theme implements RuleInterface
{
    /**
     * Rule
     *
     * @var RuleInterface
     */
    protected $rule;

    /**
     * Constructors
     *
     * @param RuleInterface $rule
     */
    public function __construct(RuleInterface $rule)
    {
        $this->rule = $rule;
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
            if ($theme->getThemePath()) {
                $params['theme_path'] = $theme->getThemePath();
                $result = array_merge($result, $this->rule->getPatternDirs($params));
            }
            $theme = $theme->getParentTheme();
        }
        return $result;
    }
}
