<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filter\DirectiveProcessor;

use Magento\Framework\DataObject;
use Magento\Framework\Filter\DirectiveProcessorInterface;
use Magento\Framework\Filter\Template;
use Magento\Framework\Filter\VariableResolverInterface;

/**
 * Allows a a directive to iterate content with the value of variables
 *
 * @example syntax {{for item in order.items}} name: {{var item.name}} {{/for}} order items collection.
 * @example syntax {{for thing in things}} {{var thing.whatever}} {{/for}} e.g.:custom collection.
 */
class ForDirective implements DirectiveProcessorInterface
{
    /**
     * @var VariableResolverInterface
     */
    private $variableResolver;

    /**
     * @param VariableResolverInterface $variableResolver
     */
    public function __construct(
        VariableResolverInterface $variableResolver
    ) {
        $this->variableResolver = $variableResolver;
    }

    /**
     * Filter the string as template.
     *
     * @param array $construction
     * @param Template $filter
     * @param array $templateVariables
     * @return string
     */
    public function process(array $construction, Template $filter, array $templateVariables): string
    {
        if (!$this->isValidLoop($construction)) {
            return $construction[0];
        }

        $loopData = $this->variableResolver->resolve($construction['loopData'], $filter, $templateVariables);

        $loopTextToReplace = $construction['loopBody'];
        $loopItemVariableName = preg_replace('/\s+/', '', $construction['loopItem']);

        if (is_array($loopData) || $loopData instanceof \Traversable) {
            return $this->getLoopReplacementText(
                $loopData,
                $loopItemVariableName,
                $loopTextToReplace,
                $filter,
                $templateVariables
            );
        }

        return $construction[0];
    }

    /**
     * Check if the matched construction is valid.
     *
     * @param array $construction
     * @return bool
     */
    private function isValidLoop(array $construction)
    {
        $requiredFields = ['loopBody', 'loopItem', 'loopData'];
        $validFields = array_filter(
            $requiredFields,
            function ($field) use ($construction) {
                return isset($construction[$field]) && strlen(trim($construction[$field]));
            }
        );
        return count($requiredFields) == count($validFields);
    }

    /**
     * Process loop text to replace.
     *
     * @param array $loopData
     * @param string $loopItemVariableName
     * @param string $loopTextToReplace
     * @param Template $filter
     * @param array $templateVariables
     * @return string
     */
    private function getLoopReplacementText(
        array $loopData,
        string $loopItemVariableName,
        string $loopTextToReplace,
        Template $filter,
        array $templateVariables
    ): string {
        $loopText = [];
        $loopIndex = 0;
        $loopDataObject = new DataObject();

        foreach ($loopData as $loopItemDataObject) {
            // Loop item can be an array or DataObject.
            // If loop item is an array, convert it to DataObject
            // to have unified interface if the collection
            if (!$loopItemDataObject instanceof DataObject) {
                if (!is_array($loopItemDataObject)) {
                    continue;
                }
                $loopItemDataObject = new DataObject($loopItemDataObject);
            }

            $loopDataObject->setData('index', $loopIndex++);
            $templateVariables['loop'] = $loopDataObject;
            $templateVariables[$loopItemVariableName] = $loopItemDataObject;

            // Current structure prohibits recursively calling template filter inside "for" directives
            if (preg_match_all(
                Template::CONSTRUCTION_PATTERN,
                $loopTextToReplace,
                $attributes,
                PREG_SET_ORDER
            )
            ) {
                $subText = $loopTextToReplace;
                foreach ($attributes as $attribute) {
                    $text = $this->variableResolver->resolve($attribute[2], $filter, $templateVariables);
                    $subText = str_replace(
                        $attribute[0],
                        is_array($text) ? $text : (string) $text,
                        $subText
                    );
                }
                $loopText[] = $subText;
            }

            unset($templateVariables[$loopItemVariableName]);
        }
        $replaceText = implode('', $loopText);

        return $replaceText;
    }

    /**
     * @inheritdoc
     */
    public function getRegularExpression(): string
    {
        return Template::LOOP_PATTERN;
    }
}
