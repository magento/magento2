<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Image;

/**
 * Convert array into string representation
 */
class ConvertImageMiscParamsToReadableFormat
{
    /**
     * Converting bool into a string representation
     *
     * @param array $miscParams
     * @return array
     */
    public function convertImageMiscParamsToReadableFormat(array $miscParams): array
    {
        $miscParams['image_height'] = 'h:' . ($miscParams['image_height'] ?? 'empty');
        $miscParams['image_width'] = 'w:' . ($miscParams['image_width'] ?? 'empty');
        $miscParams['quality'] = 'q:' . ($miscParams['quality'] ?? 'empty');
        $miscParams['angle'] = 'r:' . ($miscParams['angle'] ?? 'empty');
        $miscParams['keep_aspect_ratio'] = (!empty($miscParams['keep_aspect_ratio']) ? '' : 'non') . 'proportional';
        $miscParams['keep_frame'] = (!empty($miscParams['keep_frame']) ? '' : 'no') . 'frame';
        $miscParams['keep_transparency'] = (!empty($miscParams['keep_transparency']) ? '' : 'no') . 'transparency';
        $miscParams['constrain_only'] = (!empty($miscParams['constrain_only']) ? 'do' : 'not') . 'constrainonly';
        $miscParams['background'] = !empty($miscParams['background'])
            ? 'rgb' . implode(',', $miscParams['background'])
            : 'nobackground';
        return $miscParams;
    }
}
