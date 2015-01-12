<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\I18n\Dictionary\Options;

/**
 * Class ResolverTest
 */
class ResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $directory
     * @param bool $withContext
     * @param array $result
     * @dataProvider getOptionsDataProvider
     */
    public function testGetOptions($directory, $withContext, $result)
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\Tools\I18n\Dictionary\Options\Resolver $resolver */
        $resolver = $objectManagerHelper->getObject(
            'Magento\Tools\I18n\Dictionary\Options\Resolver',
            [
                'directory' => $directory,
                'withContext' => $withContext,
            ]
        );
        $this->assertSame($result, $resolver->getOptions());
    }

    /**
     * @return array
     */
    public function getOptionsDataProvider()
    {
        $sourceFirst = __DIR__ . '/_files/source';
        $sourceSecond = __DIR__ . '/_files/source';
        return [
            [
                $sourceFirst,
                true,
                [
                    [
                        'type' => 'php',
                        'paths' => [$sourceFirst . '/app/code/', $sourceFirst . '/app/design/'],
                        'fileMask' => '/\.(php|phtml)$/',
                    ],
                    [
                        'type' => 'js',
                        'paths' => [
                            $sourceFirst . '/app/code/',
                            $sourceFirst . '/app/design/',
                            $sourceFirst . '/lib/web/mage/',
                            $sourceFirst . '/lib/web/varien/',
                        ],
                        'fileMask' => '/\.(js|phtml)$/'
                    ],
                    [
                        'type' => 'xml',
                        'paths' => [$sourceFirst . '/app/code/', $sourceFirst . '/app/design/'],
                        'fileMask' => '/\.xml$/'
                    ]
                ],
            ],
            [
                $sourceSecond,
                false,
                [
                    ['type' => 'php', 'paths' => [$sourceSecond], 'fileMask' => '/\.(php|phtml)$/'],
                    ['type' => 'js', 'paths' => [$sourceSecond], 'fileMask' => '/\.(js|phtml)$/'],
                    ['type' => 'xml', 'paths' => [$sourceSecond], 'fileMask' => '/\.xml$/']
                ]
            ],
        ];
    }

    /**
     * @param string $directory
     * @param bool $withContext
     * @param string $message
     * @dataProvider getOptionsWrongDirDataProvider
     */
    public function testGetOptionsWrongDir($directory, $withContext, $message)
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\Tools\I18n\Dictionary\Options\Resolver $resolver */
        $resolver = $objectManagerHelper->getObject(
            'Magento\Tools\I18n\Dictionary\Options\Resolver',
            [
                'directory' => $directory,
                'withContext' => $withContext,
            ]
        );
        $this->setExpectedException('\InvalidArgumentException', $message);
        $resolver->getOptions();
    }

    /**
     * @return array
     */
    public function getOptionsWrongDirDataProvider()
    {
        return [
            ['not_exist', true, 'Specified path is not a Magento root directory'],
            ['not_exist', false, 'Specified path doesn\'t exist'],
        ];
    }
}
