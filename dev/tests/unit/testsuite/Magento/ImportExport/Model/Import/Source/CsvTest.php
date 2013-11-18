<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\ImportExport\Model\Import\Source;

class CsvTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \LogicException
     */
    public function testConstructException()
    {
        new \Magento\ImportExport\Model\Import\Source\Csv(__DIR__ . '/invalid_file');
    }

    public function testConstructStream()
    {
        $stream = 'data://text/plain;base64,' . base64_encode("column1,column2\nvalue1,value2\n");
        $model = new \Magento\ImportExport\Model\Import\Source\Csv($stream);
        foreach ($model as $value) {
            $this->assertSame(array('column1' => 'value1', 'column2' => 'value2'), $value);
        }
    }

    /**
     * @param string $delimiter
     * @param string $enclosure
     * @param array $expectedColumns
     * @dataProvider optionalArgsDataProvider
     */
    public function testOptionalArgs($delimiter, $enclosure, $expectedColumns)
    {
        $model = new \Magento\ImportExport\Model\Import\Source\Csv(
            __DIR__ . '/_files/test.csv', $delimiter, $enclosure);
        $this->assertSame($expectedColumns, $model->getColNames());
    }

    /**
     * @return array
     */
    public function optionalArgsDataProvider()
    {
        return array(
            array(',', '"', array('column1', 'column2')),
            array(',', "'", array('column1', '"column2"')),
            array('.', '"', array('column1,"column2"')),
        );
    }

    public function testRewind()
    {
        $model = new \Magento\ImportExport\Model\Import\Source\Csv(__DIR__ . '/_files/test.csv');
        $this->assertSame(-1, $model->key());
        $model->next();
        $this->assertSame(0, $model->key());
        $model->next();
        $this->assertSame(1, $model->key());
        $model->rewind();
        $this->assertSame(0, $model->key());
        $model->next();
        $model->next();
        $this->assertSame(2, $model->key());
        $this->assertSame(array('column1' => '5','column2' => ''), $model->current());
    }
}
