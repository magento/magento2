<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Fallback\Rule;

/**
 * Composite Rules
 *
 * Composite rule that represents sequence of child fallback rules
 */
class Composite implements RuleInterface
{
    /**
     * Rules
     *
     * @var RuleInterface[]
     */
    protected $rules = [];

    /**
     * Constructors
     *
     * @param RuleInterface[] $rules
     * @throws \InvalidArgumentException
     */
    public function __construct(array $rules)
    {
        foreach ($rules as $rule) {
            if (!$rule instanceof RuleInterface) {
                throw new \InvalidArgumentException('Each item should implement the fallback rule interface.');
            }
        }
        $this->rules = $rules;
    }

    /**
     * Retrieve sequentially combined directory patterns from child fallback rules
     *
     * @param array $params
     * @return array
     */
    public function getPatternDirs(array $params)
    {
        $result = [];
        foreach ($this->rules as $rule) {
            $result = array_merge($result, $rule->getPatternDirs($params));
        }
        return $result;
    }
}
