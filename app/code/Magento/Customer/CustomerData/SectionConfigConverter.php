<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\CustomerData;

use Magento\Framework\Config\ConverterInterface;

/**
 * Class that receives xml merged source and process it.
 */
class SectionConfigConverter implements ConverterInterface
{
    /**
     * Invalidate all sections marker
     */
    public const INVALIDATE_ALL_SECTIONS_MARKER = '*';

    /**
     * @inheritdoc
     */
    public function convert($source)
    {
        $sections = [];
        foreach ($source->getElementsByTagName('action') as $action) {
            $actionName = $action->getAttribute('name') === null ? '' : strtolower($action->getAttribute('name'));
            foreach ($action->getElementsByTagName('section') as $section) {
                $sections[$actionName][] = $section->getAttribute('name') === null ?
                    ''
                    : strtolower($section->getAttribute('name'));
            }
            if (!isset($sections[$actionName])) {
                $sections[$actionName][] = self::INVALIDATE_ALL_SECTIONS_MARKER;
            }
        }
        return [
            'sections' => $sections,
        ];
    }
}
