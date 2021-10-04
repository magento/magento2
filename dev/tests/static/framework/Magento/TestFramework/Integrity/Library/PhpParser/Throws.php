<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Integrity\Library\PhpParser;

/**
 * Parse throws and collect dependencies for it
 *
 */
class Throws implements ParserInterface, DependenciesCollectorInterface
{
    /**
     * @var Tokens
     */
    protected $tokens;

    /**
     * Collect dependencies
     *
     * @var array
     */
    protected $dependencies = [];

    /**
     * Save throw token key
     *
     * @var array
     */
    protected $throws = [];

    /**
     * @param Tokens $tokens
     */
    public function __construct(Tokens $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * @inheritdoc
     */
    public function parse($token, $key)
    {
        if (is_array($token) && $token[0] == T_THROW) {
            $this->throws[] = $key;
        }
    }

    /**
     * @inheritdoc
     */
    public function getDependencies(Uses $uses)
    {
        foreach ($this->throws as $throw) {
            $class = '';
            if ($this->tokens->getTokenCodeByKey($throw + 2) == T_NEW) {
                $step = 4;

                $token = $this->tokens->getTokenCodeByKey($throw + $step);
                if ($token === T_NAME_FULLY_QUALIFIED || $token === T_NAME_QUALIFIED) {
                    $class = $this->tokens->getTokenValueByKey($throw + $step);
                } else {
                    // PHP 7 compatibility
                    while ($this->tokens->getTokenCodeByKey($throw + $step) === T_STRING
                        || $this->tokens->getTokenCodeByKey($throw + $step) === T_NS_SEPARATOR) {
                        $class .= trim($this->tokens->getTokenValueByKey($throw + $step));
                        $step++;
                    }
                }

                if ($uses->hasUses()) {
                    $class = $uses->getClassNameWithNamespace($class);
                }
                $this->dependencies[] = $class;
            }
        }

        return $this->dependencies;
    }
}
