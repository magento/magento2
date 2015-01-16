<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Integrity\Library\PhpParser;

/**
 * Parse static calls and collect dependencies for it
 *
 */
class StaticCalls implements ParserInterface, DependenciesCollectorInterface
{
    /**
     * @var Tokens
     */
    protected $tokens;

    /**
     * Save static calls token key
     *
     * @var array
     */
    protected $staticCalls = [];

    /**
     * Collect dependencies
     *
     * @var array
     */
    protected $dependencies = [];

    /**
     * @param Tokens $tokens
     */
    public function __construct(Tokens $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * Check if it's foreign dependency
     *
     * @param array $token
     * @return bool
     */
    protected function isTokenClass($token)
    {
        return is_array(
            $token
        ) && !(in_array(
            $token[1],
            ['static', 'self', 'parent']
        ) || preg_match(
            '#^\$#',
            $token[1]
        ));
    }

    /**
     * @inheritdoc
     */
    public function parse($token, $key)
    {
        if (is_array(
            $token
        ) && $token[0] == T_PAAMAYIM_NEKUDOTAYIM && $this->isTokenClass(
            $this->tokens->getPreviousToken($key)
        )
        ) {
            $this->staticCalls[] = $key;
        }
    }

    /**
     * Return class name from token
     *
     * @param int $staticCall
     * @return string
     */
    protected function getClassByStaticCall($staticCall)
    {
        $step = 1;
        $staticClassParts = [];
        while ($this->tokens->getTokenCodeByKey(
            $staticCall - $step
        ) == T_STRING || $this->tokens->getTokenCodeByKey(
            $staticCall - $step
        ) == T_NS_SEPARATOR) {
            $staticClassParts[] = $this->tokens->getTokenValueByKey($staticCall - $step);
            $step++;
        }
        return implode(array_reverse($staticClassParts));
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(Uses $uses)
    {
        foreach ($this->staticCalls as $staticCall) {
            $class = $this->getClassByStaticCall($staticCall);
            if ($uses->hasUses()) {
                $class = $uses->getClassNameWithNamespace($class);
            }
            $this->dependencies[] = $class;
        }

        return $this->dependencies;
    }
}
