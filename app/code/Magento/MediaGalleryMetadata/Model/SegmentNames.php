<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model;

/**
 * Segment types to names mapper
 */
class SegmentNames
{
    private const SEGMENT_TYPE_TO_NAME = [
        0xC0 => "SOF0",
        0xC1 => "SOF1",
        0xC2 => "SOF2",
        0xC3 => "SOF4",
        0xC5 => "SOF5",
        0xC6 => "SOF6",
        0xC7 => "SOF7",
        0xC8 => "JPG",
        0xC9 => "SOF9",
        0xCA => "SOF10",
        0xCB => "SOF11",
        0xCD => "SOF13",
        0xCE => "SOF14",
        0xCF => "SOF15",
        0xC4 => "DHT",
        0xCC => "DAC",
        0xD0 => "RST0",
        0xD1 => "RST1",
        0xD2 => "RST2",
        0xD3 => "RST3",
        0xD4 => "RST4",
        0xD5 => "RST5",
        0xD6 => "RST6",
        0xD7 => "RST7",
        0xD8 => "SOI",
        0xD9 => "EOI",
        0xDA => "SOS",
        0xDB => "DQT",
        0xDC => "DNL",
        0xDD => "DRI",
        0xDE => "DHP",
        0xDF => "EXP",
        0xE0 => "APP0",
        0xE1 => "APP1",
        0xE2 => "APP2",
        0xE3 => "APP3",
        0xE4 => "APP4",
        0xE5 => "APP5",
        0xE6 => "APP6",
        0xE7 => "APP7",
        0xE8 => "APP8",
        0xE9 => "APP9",
        0xEA => "APP10",
        0xEB => "APP11",
        0xEC => "APP12",
        0xED => "APP13",
        0xEE => "APP14",
        0xEF => "APP15",
        0xF0 => "JPG0",
        0xF1 => "JPG1",
        0xF2 => "JPG2",
        0xF3 => "JPG3",
        0xF4 => "JPG4",
        0xF5 => "JPG5",
        0xF6 => "JPG6",
        0xF7 => "JPG7",
        0xF8 => "JPG8",
        0xF9 => "JPG9",
        0xFA => "JPG10",
        0xFB => "JPG11",
        0xFC => "JPG12",
        0xFD => "JPG13",
        0xFE => "COM",
        0x01 => "TEM",
        0x02 => "RES",
    ];

    /**
     * Get segment name by type
     *
     * @param int $type
     * @return string
     */
    public function getSegmentName(int $type): string
    {
        return self::SEGMENT_TYPE_TO_NAME[$type];
    }

    /**
     * Get segment type by name
     *
     * @param string $name
     * @return int
     */
    public function getSegmentType(string $name): int
    {
        return array_search($name, self::SEGMENT_TYPE_TO_NAME);
    }
}
