<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Block;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;

/**
 * Tests Magento\Search\Block\Term.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class TermTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Term
     */
    private $term;

    /**
     * Tests Magento\Search\Block\Term::GetTerms.
     *
     * @magentoDataFixture Magento/Search/_files/query.php
     * @dataProvider getTermsDataProvider
     * @param array $expected
     */
    public function testGetTerms(array $expected)
    {
        $result = $this->term->getTerms();
        $actual = array_map(
            function ($object) {
                return $object->setUpdatedAt(null)->getData();
            },
            $result
        );

        self::assertEquals(
            $expected,
            $actual
        );
    }

    /**
     * Data provider for testGetTerms.
     *
     * @return array
     */
    public function getTermsDataProvider()
    {
        return [
            [
                [
                    '1st query' =>
                        [
                            'query_id' => '1',
                            'query_text' => '1st query',
                            'num_results' => '1',
                            'popularity' => '5',
                            'redirect' => null,
                            'store_id' => '1',
                            'display_in_terms' => '1',
                            'is_active' => '1',
                            'is_processed' => '1',
                            'updated_at' => null,
                            'ratio' => 0.44444444444444,
                        ],
                    '2nd query' =>
                        [
                            'query_id' => '2',
                            'query_text' => '2nd query',
                            'num_results' => '1',
                            'popularity' => '10',
                            'redirect' => null,
                            'store_id' => '1',
                            'display_in_terms' => '1',
                            'is_active' => '1',
                            'is_processed' => '1',
                            'updated_at' => null,
                            'ratio' => 1,
                        ],
                    '3rd query' =>
                        [
                            'query_id' => '3',
                            'query_text' => '3rd query',
                            'num_results' => '1',
                            'popularity' => '1',
                            'redirect' => null,
                            'store_id' => '1',
                            'display_in_terms' => '1',
                            'is_active' => '1',
                            'is_processed' => '1',
                            'updated_at' => null,
                            'ratio' => 0,
                        ],
                ],
            ],
        ];
    }

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->term = $this->objectManager->get(
            LayoutInterface::class
        )->createBlock(
            Term::class
        );
    }
}
