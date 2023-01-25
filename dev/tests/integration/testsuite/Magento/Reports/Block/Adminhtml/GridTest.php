<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Reports\Model\ResourceModel\Product\Sold\Collection\Initial;

/**
 * Test class for \Magento\Reports\Block\Adminhtml\Grid
 * @magentoAppArea adminhtml
 */
class GridTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var $block \Magento\Reports\Block\Adminhtml\Grid
     */
    private $block;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->block = Bootstrap::getObjectManager()->get(
            \Magento\Reports\Block\Adminhtml\Grid::class
        );
    }

    public function testGetDateFormat()
    {
        $this->assertNotEmpty($this->block->getDateFormat());
    }

    /**
     *  Test apply filtering to collection
     *
     * @param string $from
     * @param string $to
     * @param string $period
     * @param string $locale
     * @param int $expected
     * @dataProvider getSalesRepresentativeIdDataProvider
     */
    public function testGetPreparedCollection($from, $to, $period, $locale, $expected)
    {
        $encodedFilter = base64_encode('report_from='. $from . '&report_to=' . $to . '&report_period=' . $period);

        $this->block->setVarNameFilter('filtername');
        /** @var $request RequestInterface */
        $request = Bootstrap::getObjectManager()->get(RequestInterface::class);
        $request->setParams(['filtername' => $encodedFilter]);
        $request->setParams(['locale' => $locale]);

        /** @var $localeResolver ResolverInterface */
        $localeResolver = Bootstrap::getObjectManager()->get(ResolverInterface::class);
        $localeResolver->setLocale();

        /** @var $initialCollection Initial */
        $initialCollection = Bootstrap::getObjectManager()->create(
            Initial::class
        );
        $this->block->setData(['dataSource' => $initialCollection]);

        /** @var $collection Initial */
        $collection = $this->block->getPreparedCollection();
        $items = $collection->getItems();
        $this->assertCount($expected, $items);
    }

    /**
     * Data provider for testGetPreparedCollection method.
     *
     * @return array
     */
    public function getSalesRepresentativeIdDataProvider()
    {
        return [
            'Data for US locale' =>             ['08/15/2018', '08/20/2018', 'day', 'en_US', 6],
            'Data for Australian locale' =>     ['15/08/2018', '31/08/2018', 'day', 'en_AU', 17],
            'Data for French locale' =>         ['20.08.2018', '30.08.2018', 'day', 'fr_FR', 11],
        ];
    }
}
