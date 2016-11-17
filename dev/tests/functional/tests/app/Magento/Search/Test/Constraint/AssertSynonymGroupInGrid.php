<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\Constraint;

use Magento\Search\Test\Fixture\SynonymGroup;
use Magento\Search\Test\Page\Adminhtml\SynonymGroupIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Assert that created Synonym Group can be found in grid.
 */
class AssertSynonymGroupInGrid extends AbstractConstraint
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * Assert that created Synonym Group can be found in grid via: synonyms.
     *
     * @param SynonymGroup $synonymGroup
     * @param SynonymGroupIndex $synonymGroupIndex
     * @param array|null $synonymFilter
     * @return void
     */
    public function processAssert(
        SynonymGroup $synonymGroup,
        SynonymGroupIndex $synonymGroupIndex,
        $synonymFilter = null
    ) {
        $synonymGroupIndex->open();

        $this->prepareFilter($synonymGroup, $synonymFilter);
        $synonymGroupIndex->getSynonymGroupGrid()->search($this->filter);

        \PHPUnit_Framework_Assert::assertTrue(
            $synonymGroupIndex->getSynonymGroupGrid()->isRowVisible($this->filter, false, false),
            'Synonym Group is absent in Synonym grid'
        );

        \PHPUnit_Framework_Assert::assertEquals(
            count($synonymGroupIndex->getSynonymGroupGrid()->getAllIds()),
            1,
            'There is more than one synonyms founded'
        );
    }

    /**
     * Prepare filter for search synonyms.
     *
     * @param SynonymGroup $synonymGroup
     * @param array|null $synonymFilter
     * @return array
     */
    private function prepareFilter(SynonymGroup $synonymGroup, $synonymFilter = null)
    {
        $data = $synonymGroup->getData();
        $this->filter = [
            'synonyms' => $data['synonyms'],
            'website_id' => isset($synonymFilter['data']['website_id'])
                ? $synonymFilter['data']['website_id']
                : '',
            'group_id' => isset($synonymFilter['data']['group_id'])
                ? $this->getGroupId($data['synonyms'])
                : '',
        ];
    }

    /**
     * Get group id by synonym.
     *
     * @param string $synonym
     * @return int|null
     */
    public function getGroupId($synonym)
    {
        $url = $_ENV['app_backend_url'] . 'mui/index/render/';
        $data = [
            'namespace' => 'search_synonyms_grid',
            'filters' => [
                'placeholder' => true,
                'synonyms' => $synonym
            ],
            'isAjax' => true
        ];
        $config = \Magento\Mtf\ObjectManagerFactory::getObjectManager()->get(\Magento\Mtf\Config\DataInterface::class);
        $curl = new BackendDecorator(new CurlTransport(), $config);

        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();

        preg_match('/search_synonyms_grid_data_source.+items.+"group_id":"(\d+)"/', $response, $match);
        return empty($match[1]) ? null : $match[1];
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Synonym Group is present in grid.';
    }
}
