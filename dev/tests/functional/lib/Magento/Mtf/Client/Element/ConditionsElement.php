<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Client\Element;

use Magento\Mtf\ObjectManager;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\ElementInterface;

/**
 * Typified element class for conditions.
 *
 * Format value.
 * Add slash to symbols: "{", "}", "[", "]", ":".
 * 1. Single condition:
 * [Type|Param|Param|...|Param]
 * 2. List conditions:
 * [Type|Param|Param|...|Param]
 * [Type|Param|Param|...|Param]
 * [Type|Param|Param|...|Param]
 * 3. Combination condition with single condition
 * {Type|Param|Param|...|Param:[Type|Param|Param|...|Param]}
 * 4. Combination condition with list conditions
 * {Type|Param|Param|...|Param:[[Type|Param|...|Param][Type|Param|...|Param]...[Type|Param|...|Param]]}
 * 5. Top level condition
 * {TopLevelCondition:[ANY|FALSE]}{Type|Param|Param|...|Param:[[Type|Param|...|Param]...[Type|Param|...|Param]]}
 *
 * Example value:
 * {Products subselection|total amount|greater than|135|ANY:[[Price in cart|is|100][Quantity in cart|is|100]]}
 * {Conditions combination:[
 *     [Subtotal|is|100]
 *     {Product attribute combination|NOT FOUND|ANY:[[Attribute Set|is|Default][Attribute Set|is|Default]]}
 * ]}
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ConditionsElement extends SimpleElement
{
    /**
     * Count for trying fill condition element.
     */
    const TRY_COUNT = 3;

    /**
     * Main condition.
     *
     * @var string
     */
    protected $mainCondition = './/ul[contains(@id,"__1__children")]/..';

    /**
     * Identification for chooser grid.
     *
     * @var string
     */
    protected $chooserLocator = '.rule-chooser-trigger';

    /**
     * Button add condition.
     *
     * @var string
     */
    protected $addNew = './/*[contains(@class,"rule-param-new-child")]/a';

    /**
     * Button remove condition.
     *
     * @var string
     */
    protected $remove = './/*/a[@class="rule-param-remove"]';

    /**
     * New condition.
     *
     * @var string
     */
    protected $newCondition = './ul/li/span[contains(@class,"rule-param-new-child")]/..';

    /**
     * Type of new condition.
     *
     * @var string
     */
    protected $typeNew = './/*[@class="element"]/select';

    /**
     * Created condition.
     *
     * @var string
     */
    protected $created = './ul/li[span[contains(@class,"rule-param-new-child")]]/preceding-sibling::li[1]';

    /**
     * Children condition.
     *
     * @var string
     */
    protected $children = './/ul[contains(@id,"conditions__")]';

    /**
     * Parameter of condition.
     *
     * @var string
     */
    protected $param = './span[span[*[substring(@id,(string-length(@id)-%d+1))="%s"]]]';

    /**
     * Rule param wait locator.
     *
     * @var string
     */
    protected $ruleParamWait = './/*[@class="rule-param-wait"]';

    /**
     * Rule param input selector.
     *
     * @var string
     */
    protected $ruleParamInput = '[name^="rule"]';

    /**
     * Apply rule param link.
     *
     * @var string
     */
    protected $applyRuleParam = './/*[@class="rule-param-apply"]';

    /**
     * Chooser grid locator.
     *
     * @var string
     */
    protected $chooserGridLocator = 'div[id*=chooser]';

    /**
     * Key of last find param.
     *
     * @var int
     */
    protected $findKeyParam = 0;

    /**
     * Map of parameters.
     *
     * @var array
     */
    protected $mapParams = [
        'attribute',
        'operator',
        'value_type',
        'value',
        'aggregator',
    ];

    /**
     * Map encode special chars.
     *
     * @var array
     */
    protected $encodeChars = [
        '\{' => '&lbrace;',
        '\}' => '&rbrace;',
        '\[' => '&lbracket;',
        '\]' => '&rbracket;',
        '\:' => '&colon;',
    ];

    /**
     * Map decode special chars.
     *
     * @var array
     */
    protected $decodeChars = [
        '&lbrace;' => '{',
        '&rbrace;' => '}',
        '&lbracket;' => '[',
        '&rbracket;' => ']',
        '&colon;' => ':',
    ];

    /**
     * Latest occurred exception.
     *
     * @var \Exception
     */
    protected $exception;

    /**
     * Set value to conditions.
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $this->eventManager->dispatchEvent(['set_value'], [__METHOD__, $this->getAbsoluteSelector()]);
        $this->clear();
        $conditions = $this->decodeValue($value);
        $context = $this->find($this->mainCondition, Locator::SELECTOR_XPATH);
        if (!empty($conditions[0]['TopLevelCondition'])) {
            array_unshift($this->mapParams, 'aggregator');
            $condition = $this->parseTopLevelCondition($conditions[0]['TopLevelCondition']);
            $this->fillCondition($condition['rules'], $context);
            unset($conditions[0]);
            array_shift($this->mapParams);
        }
        $this->addMultipleCondition($conditions, $context);
    }

    /**
     * Add conditions combination.
     *
     * @param string $condition
     * @param ElementInterface $context
     * @return ElementInterface
     */
    protected function addConditionsCombination($condition, ElementInterface $context)
    {
        $condition = $this->parseCondition($condition);
        $this->addCondition($condition['type'], $context);
        $createdCondition = $context->find($this->created, Locator::SELECTOR_XPATH);
        $this->waitForCondition($createdCondition);
        if (!empty($condition['rules'])) {
            $this->fillCondition($condition['rules'], $createdCondition);
        }
        return $createdCondition;
    }

    /**
     * Add conditions.
     *
     * @param array $conditions
     * @param ElementInterface $context
     * @return void
     */
    protected function addMultipleCondition(array $conditions, ElementInterface $context)
    {
        foreach ($conditions as $key => $condition) {
            $elementContext = is_numeric($key) ? $context : $this->addConditionsCombination($key, $context);
            if (is_string($condition)) {
                $this->addSingleCondition($condition, $elementContext);
            } else {
                $this->addMultipleCondition($condition, $elementContext);
            }
        }
    }

    /**
     * Add single Condition.
     *
     * @param string $condition
     * @param ElementInterface $context
     * @return void
     */
    protected function addSingleCondition($condition, ElementInterface $context)
    {
        $condition = $this->parseCondition($condition);
        $this->addCondition($condition['type'], $context);
        $createdCondition = $context->find($this->created, Locator::SELECTOR_XPATH);
        $this->waitForCondition($createdCondition);
        $this->fillCondition($condition['rules'], $createdCondition);
    }

    /**
     * Click to add condition button and set type.
     *
     * @param string $type
     * @param ElementInterface $context
     * @return void
     * @throws \Exception
     */
    protected function addCondition($type, ElementInterface $context)
    {
        $newCondition = $context->find($this->newCondition, Locator::SELECTOR_XPATH);
        $count = 0;

        do {
            $newCondition->find($this->addNew, Locator::SELECTOR_XPATH)->click();

            try {
                $newCondition->find($this->typeNew, Locator::SELECTOR_XPATH, 'select')->setValue($type);
                $isSetType = true;
            } catch (\PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
                $isSetType = false;
                $this->exception = $e;
                $this->eventManager->dispatchEvent(['exception'], [__METHOD__, $this->getAbsoluteSelector()]);
            }
            $count++;
        } while (!$isSetType && $count < self::TRY_COUNT);

        if (!$isSetType) {
            $exception = $this->exception ? $this->exception : (new \Exception("Can not add condition: {$type}"));
            throw $exception;
        }
    }

    /**
     * Fill single condition.
     *
     * @param array $rules
     * @param ElementInterface $element
     * @return void
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function fillCondition(array $rules, ElementInterface $element)
    {
        $this->resetKeyParam();
        foreach ($rules as $rule) {
            /** @var ElementInterface $param */
            $param = $this->findNextParam($element);
            $isSet = false;
            $count = 0;

            do {
                try {
                    $openParamLink = $param->find('a');
                    if ($openParamLink->isVisible()) {
                        $openParamLink->click();
                    }
                    $this->waitUntil(function () use ($param) {
                        return $param->find($this->ruleParamInput)->isVisible() ? true : null;
                    });

                    if ($this->fillGrid($rule, $param)) {
                        $isSet = true;
                    } elseif ($this->fillSelect($rule, $param)) {
                        $isSet = true;
                    } elseif ($this->fillText($rule, $param)) {
                        $isSet = true;
                    }
                } catch (\PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
                    $isSet = false;
                    $this->exception = $e;
                    $this->eventManager->dispatchEvent(['exception'], [__METHOD__, $this->getAbsoluteSelector()]);
                }
                $count++;
            } while (!$isSet && $count < self::TRY_COUNT);

            if (!$isSet) {
                $exception = $this->exception ? $this->exception : (new \Exception('Can not set value: ' . $rule));
                throw $exception;
            }
        }
    }

    /**
     * Fill grid element.
     *
     * @param string $rule
     * @param ElementInterface $param
     * @return bool
     */
    protected function fillGrid($rule, ElementInterface $param)
    {
        if (preg_match('`%(.*?)%`', $rule, $chooserGrid)) {
            $chooserConfig = explode('#', $chooserGrid[1]);
            $rule = preg_replace('`%(.*?)%`', '', $rule);

            $param->find($this->chooserLocator)->click();
            $grid = ObjectManager::getInstance()->create(
                str_replace('/', '\\', $chooserConfig[0]),
                [
                    'element' => $this->find($this->chooserGridLocator)
                ]
            );
            $grid->searchAndSelect([$chooserConfig[1] => $rule]);

            $apply = $param->find($this->applyRuleParam, Locator::SELECTOR_XPATH);
            if ($apply->isVisible()) {
                $apply->click();
            }

            return true;
        }
        return false;
    }

    /**
     * Fill select element.
     *
     * @param string $rule
     * @param ElementInterface $param
     * @return bool
     */
    protected function fillSelect($rule, ElementInterface $param)
    {
        $value = $param->find('select', Locator::SELECTOR_TAG_NAME, 'select');
        if ($value->isVisible()) {
            $value->setValue($rule);
            $this->click();

            return true;
        }
        return false;
    }

    /**
     * Fill text element.
     *
     * @param string $rule
     * @param ElementInterface $param
     * @return bool
     */
    protected function fillText($rule, ElementInterface $param)
    {
        $value = $param->find('input', Locator::SELECTOR_TAG_NAME);
        if ($value->isVisible()) {
            $value->setValue($rule);

            $apply = $param->find('.//*[@class="rule-param-apply"]', Locator::SELECTOR_XPATH);
            if ($apply->isVisible()) {
                $apply->click();
            }

            return true;
        }
        return false;
    }

    /**
     * Decode value.
     *
     * @param string $value
     * @return array
     * @throws \Exception
     */
    protected function decodeValue($value)
    {
        $value = str_replace(array_keys($this->encodeChars), $this->encodeChars, $value);
        $value = preg_replace('/(\]|})({|\[)/', '$1,$2', $value);
        $value = preg_replace('/{([^:]+):/', '{"$1":', $value);
        $value = preg_replace('/\[([^\[{])/', '"$1', $value);
        $value = preg_replace('/([^\]}])\]/', '$1"', $value);
        $value = str_replace(array_keys($this->decodeChars), $this->decodeChars, $value);
        $value = "[{$value}]";
        $value = json_decode($value, true);
        if (null === $value) {
            throw new \Exception('Bad format value.');
        }
        return $value;
    }

    /**
     * Parse condition.
     *
     * @param string $condition
     * @return array
     * @throws \Exception
     */
    protected function parseCondition($condition)
    {
        if (!preg_match_all('/([^|]+\|?)/', $condition, $match)) {
            throw new \Exception('Bad format condition');
        }
        foreach ($match[1] as $key => $value) {
            $match[1][$key] = rtrim($value, '|');
        }

        return [
            'type' => array_shift($match[1]),
            'rules' => array_values($match[1]),
        ];
    }

    /**
     * Parse top level condition.
     *
     * @param string $condition
     * @return array
     * @throws \Exception
     */
    protected function parseTopLevelCondition($condition)
    {
        if (!preg_match_all('/([^|]+\|?)/', $condition, $match)) {
            throw new \Exception('Bad format condition');
        }
        foreach ($match[1] as $key => $value) {
            $match[1][$key] = rtrim($value, '|');
        }

        return [
            'rules' => $match[1],
        ];
    }

    /**
     * Find next param of condition for fill.
     *
     * @param ElementInterface $context
     * @return ElementInterface
     * @throws \Exception
     */
    protected function findNextParam(ElementInterface $context)
    {
        do {
            if (!isset($this->mapParams[$this->findKeyParam])) {
                throw new \Exception("Empty map of params");
            }
            $param = $this->mapParams[$this->findKeyParam];
            $element = $context->find(sprintf($this->param, strlen($param), $param), Locator::SELECTOR_XPATH);
            $this->findKeyParam += 1;
        } while (!$element->isVisible());

        return $element;
    }

    /**
     * Reset key of last find param.
     *
     * @return void
     */
    protected function resetKeyParam()
    {
        $this->findKeyParam = 0;
    }

    /**
     * Param wait loader.
     *
     * @param ElementInterface $element
     * @return bool|null
     */
    protected function waitForCondition(ElementInterface $element)
    {
        $this->waitUntil(
            function () use ($element) {
                return $element->getAttribute('class') == 'rule-param-wait' ? null : true;
            }
        );
    }

    /**
     * Clear conditions.
     *
     * @return void
     */
    protected function clear()
    {
        $remote = $this->find($this->remove, Locator::SELECTOR_XPATH);
        while ($remote->isVisible()) {
            $remote->click();
            $remote = $this->find($this->remove, Locator::SELECTOR_XPATH);
        }
    }

    /**
     * Get value from conditions.
     *
     * @return null
     */
    public function getValue()
    {
        return null;
    }
}
