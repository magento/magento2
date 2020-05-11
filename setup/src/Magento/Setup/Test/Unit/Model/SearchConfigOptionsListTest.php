<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Model\SearchConfigOptionsList;
use PHPUnit\Framework\TestCase;

class SearchConfigOptionsListTest extends TestCase
{
    /**
     * @var SearchConfigOptionsList
     */
    private $searchConfigOptionsList;

    protected function setup(): void
    {
        $objectManager = new ObjectManager($this);
        $this->searchConfigOptionsList = $objectManager->getObject(SearchConfigOptionsList::class);
    }

    public function testGetOptionsList()
    {
        $optionsList = $this->searchConfigOptionsList->getOptionsList();
        $this->assertCount(8, $optionsList);

        $this->assertArrayHasKey(0, $optionsList);
        $this->assertInstanceOf(SelectConfigOption::class, $optionsList[0]);
        $this->assertEquals('search-engine', $optionsList[0]->getName());
        $this->assertCount(3, $optionsList[0]->getSelectOptions());
        $this->assertContains('elasticsearch5', $optionsList[0]->getSelectOptions());
        $this->assertContains('elasticsearch6', $optionsList[0]->getSelectOptions());
        $this->assertContains('elasticsearch7', $optionsList[0]->getSelectOptions());

        $this->assertArrayHasKey(1, $optionsList);
        $this->assertInstanceOf(TextConfigOption::class, $optionsList[1]);
        $this->assertEquals('elasticsearch-host', $optionsList[1]->getName());

        $this->assertArrayHasKey(2, $optionsList);
        $this->assertInstanceOf(TextConfigOption::class, $optionsList[2]);
        $this->assertEquals('elasticsearch-port', $optionsList[2]->getName());

        $this->assertArrayHasKey(3, $optionsList);
        $this->assertInstanceOf(TextConfigOption::class, $optionsList[3]);
        $this->assertEquals('elasticsearch-enable-auth', $optionsList[3]->getName());

        $this->assertArrayHasKey(4, $optionsList);
        $this->assertInstanceOf(TextConfigOption::class, $optionsList[4]);
        $this->assertEquals('elasticsearch-username', $optionsList[4]->getName());

        $this->assertArrayHasKey(5, $optionsList);
        $this->assertInstanceOf(TextConfigOption::class, $optionsList[5]);
        $this->assertEquals('elasticsearch-password', $optionsList[5]->getName());

        $this->assertArrayHasKey(6, $optionsList);
        $this->assertInstanceOf(TextConfigOption::class, $optionsList[6]);
        $this->assertEquals('elasticsearch-index-prefix', $optionsList[6]->getName());

        $this->assertArrayHasKey(7, $optionsList);
        $this->assertInstanceOf(TextConfigOption::class, $optionsList[7]);
        $this->assertEquals('elasticsearch-timeout', $optionsList[7]->getName());
    }
}
