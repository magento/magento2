<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Data fixture directives parser service
 */
class DataFixtureDirectivesParser
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param Json $serializer
     */
    public function __construct(
        Json $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * Parse data fixture directives
     *
     * @param string $fixture
     * @return array
     * @throws LocalizedException
     */
    public function parse(string $fixture): array
    {
        list($name, $directives) = array_pad(array_values(array_filter(explode(' ', $fixture, 2))), 2, '');
        $id = null;
        $data = [];
        if ($directives) {
            $json = '{}';
            $with = strpos($directives, 'with:');
            if ($with !== false) {
                $jsonStart = $with + 5;
                $jsonEnd = strrpos($directives, '}');
                $json = trim(substr($directives, $jsonStart, $jsonEnd - $jsonStart + 1));
                $directives = substr_replace($directives, '', $jsonStart, $jsonEnd - $jsonStart + 1);
            }
            foreach (array_filter(explode(' ', $directives)) as $pair) {
                list($directive, $value) = explode(':', $pair, 2);
                switch ($directive) {
                    case 'with':
                        $data = $this->serializer->unserialize($json);
                        break;
                    case 'as':
                        $id = $value;
                        break;
                    default:
                        throw new \InvalidArgumentException("Unknown data fixture directive '$directive'");
                }
            }
        }
        if (strpos($name, '\\') !== false && !class_exists($name) && !is_callable($name)) {
            // usage of a single directory separator symbol streamlines search across the source code
            throw new LocalizedException(__('Directory separator "\\" is prohibited in fixture declaration.'));
        }

        return [
            'identifier' => $id,
            'name' => $name,
            'data' => $data,
        ];
    }
}
