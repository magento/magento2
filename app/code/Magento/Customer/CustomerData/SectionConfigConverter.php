<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\CustomerData;

class SectionConfigConverter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Invalidate all sections marker
     */
    const INVALIDATE_ALL_SECTIONS_MARKER = '*';

    /**
     * {@inheritdoc}
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
