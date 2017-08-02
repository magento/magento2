<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Config\Structure;

/**
 * PayPal change structure of payment methods configuration in admin panel.
 * @since 2.2.0
 */
class PaymentSectionModifier
{
    /**
     * Identifiers of special payment method configuration groups
     *
     * @var array
     * @since 2.2.0
     */
    private static $specialGroups = [
        'account',
        'recommended_solutions',
        'other_paypal_payment_solutions',
        'other_payment_methods',
    ];

    /**
     * Returns changed section structure.
     *
     * Payment configuration has predefined special blocks:
     *  - Account information (id = account),
     *  - Recommended Solutions (id = recommended_solutions),
     *  - Other PayPal paymnt solution (id = other_paypal_payment_solutions),
     *  - Other payment methods (id = other_payment_methods).
     * All payment methods configuration should be moved to one of this group.
     * To move payment method to specific configuration group specify "displayIn"
     * attribute in system.xml file equals to any id of predefined special group.
     * If "displayIn" attribute is not specified then payment method moved to "Other payment methods" group
     *
     * @param array $initialStructure
     * @return array
     * @since 2.2.0
     */
    public function modify(array $initialStructure)
    {
        $changedStructure = array_fill_keys(self::$specialGroups, []);

        foreach ($initialStructure as $childSection => $childData) {
            if (in_array($childSection, self::$specialGroups)) {
                if (isset($changedStructure[$childSection]['children'])) {
                    $children = $changedStructure[$childSection]['children'];
                    if (isset($childData['children'])) {
                        $children += $childData['children'];
                    }
                    $childData['children'] = $children;
                    unset($children);
                }
                $changedStructure[$childSection] = $childData;
            } else {
                $moveInstructions = $this->getMoveInstructions($childSection, $childData);
                if (!empty($moveInstructions)) {
                    foreach ($moveInstructions as $moveInstruction) {
                        unset($childData['children'][$moveInstruction['section']]);
                        unset($moveInstruction['data']['displayIn']);
                        $changedStructure
                            [$moveInstruction['parent']]
                                ['children']
                                    [$moveInstruction['section']] = $moveInstruction['data'];
                    }
                }
                if (!isset($moveInstructions[$childSection])) {
                    $changedStructure['other_payment_methods']['children'][$childSection] = $childData;
                }
            }
        }

        return $changedStructure;
    }

    /**
     * Recursively collect groups that should be moved to special section
     *
     * @param string $section
     * @param array $data
     * @return array
     * @since 2.2.0
     */
    private function getMoveInstructions($section, $data)
    {
        $moved = [];

        if (array_key_exists('children', $data)) {
            foreach ($data['children'] as $childSection => $childData) {
                $movedChildren = $this->getMoveInstructions($childSection, $childData);
                if (isset($movedChildren[$childSection])) {
                    unset($data['children'][$childSection]);
                }
                $moved = array_merge($moved, $movedChildren);
            }
        }

        if (isset($data['displayIn']) && in_array($data['displayIn'], self::$specialGroups)) {
            $moved = array_merge(
                [
                    $section => [
                    'parent' => $data['displayIn'],
                    'section' => $section,
                    'data' => $data
                    ]
                ],
                $moved
            );
        }

        return $moved;
    }
}
