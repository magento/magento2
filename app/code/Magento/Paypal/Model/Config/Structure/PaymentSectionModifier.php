<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Config\Structure;

/**
 * PayPal change structure of payment methods configuration in admin panel.
 */
class PaymentSectionModifier
{
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
     */
    public function modify(array $initialStructure)
    {
        $changedStructure = [
            'account' => [],
            'recommended_solutions' => [],
            'other_paypal_payment_solutions' => [],
            'other_payment_methods' => []
        ];

        foreach ($initialStructure as $childSection => $childData) {
            if (array_key_exists($childSection, $changedStructure)) {
                if (isset($changedStructure[$childSection]['children'])) {
                    $children = $changedStructure[$childSection]['children'];
                    if (isset($childData['children'])) {
                        $children += $childData['children'];
                    }
                    $childData['children'] = $children;
                    unset($children);
                }
                $changedStructure[$childSection] = $childData;
            } elseif ($displayIn = $this->getDisplayInSection($childSection, $childData)) {
                $changedStructure[$displayIn['parent']]['children'][$displayIn['section']] = $displayIn['data'];
            } else {
                $changedStructure['other_payment_methods']['children'][$childSection] = $childData;
            }
        }

        return $changedStructure;
    }

    /**
     * Recursive search of "displayIn" element in node children
     *
     * @param string $section
     * @param array $data
     * @return array|null
     */
    private function getDisplayInSection($section, $data)
    {
        if (is_array($data) && array_key_exists('displayIn', $data)) {
            return [
                'parent' => $data['displayIn'],
                'section' => $section,
                'data' => $data
            ];
        }

        if (array_key_exists('children', $data)) {
            foreach ($data['children'] as $childSection => $childData) {
                return $this->getDisplayInSection($childSection, $childData);
            }
        }

        return null;
    }
}
