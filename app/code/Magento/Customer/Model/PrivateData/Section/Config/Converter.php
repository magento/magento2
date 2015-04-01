<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\PrivateData\Section\Config;

/**
 * Section Config Converter
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
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
            $actionName = $action->getAttribute('name');
            foreach ($action->getElementsByTagName('section') as $section) {
                $sections[$actionName][] = $section->getAttribute('name');
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
