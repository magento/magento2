<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Client\Element;

use Magento\Mtf\ObjectManager;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\ElementInterface;

/**
 * Class ConditionsElement
 * Typified element class for conditions
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
     * Main condition
     *
     * @var string
     */
    protected $mainCondition = './/ul[contains(@id,"__1__children")]/..';

    /**
     * Identification for chooser grid
     *
     * @var string
     */
    protected $chooserLocator = '.rule-chooser-trigger';

    /**
     * Button add condition
     *
     * @var string
     */
    protected $addNew = './/*[contains(@class,"rule-param-new-child")]/a';

    /**
     * Button remove condition
     *
     * @var string
     */
    protected $remove = './/*/a[@class="rule-param-remove"]';

    /**
     * New condition
     *
     * @var string
     */
    protected $newCondition = './ul/li/span[contains(@class,"rule-param-new-child")]/..';

    /**
     * Type of new condition
     *
     * @var string
     */
    protected $typeNew = './/*[@class="element"]/select';

    /**
     * Created condition
     *
     * @var string
     */
    protected $created = './ul/li[span[contains(@class,"rule-param-new-child")]]/preceding-sibling::li[1]';

    /**
     * Children condition
     *
     * @var string
     */
    protected $children = './/ul[contains(@id,"conditions__")]';

    /**
     * Parameter of condition
     *
     * @var string
     */
    protected $param = './span[span[*[substring(@id,(string-length(@id)-%d+1))="%s"]]]';

    /**
     * Key of last find param
     *
     * @var int
     */
    protected $findKeyParam = 0;

    /**
     * Map of parameters
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
     * Map encode special chars
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
     * Map decode special chars
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
     * Rule param wait locator
     *
     * @var string
     */
    protected $ruleParamWait = './/*[@class="rule-param-wait"]';

    /**
     * Chooser grid locator
     *
     * @var string
     */
    protected $chooserGridLocator = 'div[id*=chooser]';

    /**
     * Rule param input selector.
     *
     * @var string
     */
    protected $ruleParamInput = '.element [name^="rule"]';

    /**
     * Set value to conditions
     *
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $conditions = $this->decodeValue($value);
        $context = $this->find($this->mainCondition, Locator::SELECTOR_XPATH);
        $this->clear();
        $this->addMultipleCondition($conditions, $context);
    }

    /**
     * Add condition combination
     *
     * @param string $condition
     * @param ElementInterface $context
     * @return ElementInterface
     */
    protected function addConditionsCombination($condition, ElementInterface $context)
    {
        $condition = $this->parseCondition($condition);

        $this->driver->selectWindow();
        $newCondition = $context->find($this->newCondition, Locator::SELECTOR_XPATH);
        $newCondition->find($this->addNew, Locator::SELECTOR_XPATH)->click();

        $this->driver->selectWindow();
        $typeNewCondition = $newCondition->find($this->typeNew, Locator::SELECTOR_XPATH, 'select');
        $typeNewCondition->setValue($condition['type']);

        $createdCondition = $context->find($this->created, Locator::SELECTOR_XPATH);
        $this->waitForCondition($createdCondition);
        if (!empty($condition['rules'])) {
            $this->fillCondition($condition['rules'], $createdCondition);
        }
        return $createdCondition;
    }

    /**
     * Add conditions
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
     * Add single Condition
     *
     * @param string $condition
     * @param ElementInterface $context
     * @return void
     */
    protected function addSingleCondition($condition, ElementInterface $context)
    {
        $condition = $this->parseCondition($condition);

        $this->driver->selectWindow();
        $newCondition = $context->find($this->newCondition, Locator::SELECTOR_XPATH);
        $newCondition->find($this->addNew, Locator::SELECTOR_XPATH)->click();

        $typeNew = $this->typeNew;
        $newCondition->waitUntil(
            function () use ($newCondition, $typeNew) {
                $element = $newCondition->find($typeNew, Locator::SELECTOR_XPATH, 'select');
                if ($element->isVisible()) {
                    return true;
                }
                $this->driver->selectWindow();
                return null;
            }
        );
        $newCondition->find($this->typeNew, Locator::SELECTOR_XPATH, 'select')->setValue($condition['type']);

        $createdCondition = $context->find($this->created, Locator::SELECTOR_XPATH);
        $this->waitForCondition($createdCondition);
        $this->fillCondition($condition['rules'], $createdCondition);
    }

    /**
     * Fill single condition
     *
     * @param array $rules
     * @param ElementInterface $element
     * @return void
     * @throws \Exception
     */
    protected function fillCondition(array $rules, ElementInterface $element)
    {
        $this->resetKeyParam();
        foreach ($rules as $rule) {
            /** @var ElementInterface $param */
            $param = $this->findNextParam($element);

            $this->driver->selectWindow();
            $param->find('a')->click();

            if (preg_match('`%(.*?)%`', $rule, $chooserGrid)) {
                $chooserConfig = explode('#', $chooserGrid[1]);
                $param->find($this->chooserLocator)->click();
                $rule = preg_replace('`%(.*?)%`', '', $rule);
                $grid = ObjectManager::getInstance()->create(
                    str_replace('/', '\\', $chooserConfig[0]),
                    [
                        'element' => $this->find($this->chooserGridLocator)
                    ]
                );
                $grid->searchAndSelect([$chooserConfig[1] => $rule]);
                continue;
            }
            $input = $this->ruleParamInput;
            $param->waitUntil(
                function () use ($param, $input) {
                    $element = $param->find($input);
                    return $element->isVisible() ? true : null;
                }
            );
            $value = $param->find('select', Locator::SELECTOR_TAG_NAME, 'select');
            if ($value->isVisible()) {
                $value->setValue($rule);
                $this->click();
                continue;
            }
            $value = $param->find('input', Locator::SELECTOR_TAG_NAME);
            if ($value->isVisible()) {
                $value->setValue($rule);

                $apply = $param->find('.//*[@class="rule-param-apply"]', Locator::SELECTOR_XPATH);
                if ($apply->isVisible()) {
                    $apply->click();
                }
                continue;
            }
            throw new \Exception('Undefined type of value ');
        }
    }

    /**
     * Decode value
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
     * Parse condition
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
     * Find next param of condition for fill
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
     * Reset key of last find param
     *
     * @return void
     */
    protected function resetKeyParam()
    {
        $this->findKeyParam = 0;
    }

    /**
     * Param wait loader
     *
     * @return void
     */
    protected function waitForCondition(ElementInterface $element)
    {
        $this->waitUntil(
            function () use ($element) {
                if ($element->getAttribute('class') == 'rule-param-wait') {
                    $this->driver->selectWindow();
                    return null;
                }
                return true;
            }
        );
    }

    /**
     * Clear conditions
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
     * Get value from conditions
     *
     * @return null
     */
    public function getValue()
    {
        return null;
    }
}
