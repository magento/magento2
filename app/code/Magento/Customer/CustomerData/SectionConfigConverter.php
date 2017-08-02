<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\CustomerData;

/**
 * Class \Magento\Customer\CustomerData\SectionConfigConverter
 *
 * @since 2.0.0
 */
class SectionConfigConverter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Invalidate all sections marker
     */
    const INVALIDATE_ALL_SECTIONS_MARKER = '*';

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function convert($source)
    {
        $sections = [];
        foreach ($source->getElementsByTagName('action') as $action) {
            $actionName = strtolower($action->getAttribute('name'));
            foreach ($action->getElementsByTagName('section') as $section) {
                $sections[$actionName][] = strtolower($section->getAttribute('name'));
            }
            if (!isset($sections[$actionName])) {
                $sections[$actionName] = self::INVALIDATE_ALL_SECTIONS_MARKER;
            }
        }
        return [
            'sections' => $sections,
        ];
    }
}
