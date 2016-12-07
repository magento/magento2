<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\DeploymentConfig\Writer;

use \Magento\Framework\App\DeploymentConfig\Writer\PhpFormatter;

class PhpFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider formatWithCommentDataProvider
     * @param string|array $data
     * @param array $comments
     * @param string $expectedResult
     */
    public function testFormat($data, $comments, $expectedResult)
    {
        $formatter = new PhpFormatter();
        $this->assertEquals($expectedResult, $formatter->format($data, $comments));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function formatWithCommentDataProvider()
    {
        $array = [
            'ns1' => [
                's1' => [
                    's11',
                    's12'
                ],
                's2' => [
                    's21',
                    's22'
                ],
            ],
            'ns2' => [
                's1' => [
                    's11'
                ],
            ],
            'ns3' => 'just text',
            'ns4' => 'just text'
        ];
        $comments1 = ['ns2' => 'comment for namespace 2'];
        $comments2 = [
            'ns1' => 'comment for namespace 1',
            'ns2' => "comment for namespace 2.\nNext comment for namespace 2",
            'ns3' => 'comment for namespace 3',
            'ns4' => 'comment for namespace 4',
            'ns5' => 'comment for unexisted namespace 5',
        ];
        $expectedResult1 = <<<TEXT
<?php
return array (
  'ns1' => 
  array (
    's1' => 
    array (
      0 => 's11',
      1 => 's12',
    ),
    's2' => 
    array (
      0 => 's21',
      1 => 's22',
    ),
  ),
  /**
   * 'comment for namespace 2'
   */
  'ns2' => 
  array (
    's1' => 
    array (
      0 => 's11',
    ),
  ),
  'ns3' => 'just text',
  'ns4' => 'just text'
);

TEXT;
        $expectedResult2 = <<<TEXT
<?php
return array (
  /**
   * 'comment for namespace 1'
   */
  'ns1' => 
  array (
    's1' => 
    array (
      0 => 's11',
      1 => 's12',
    ),
    's2' => 
    array (
      0 => 's21',
      1 => 's22',
    ),
  ),
  /**
   * 'comment for namespace 2.
   * Next comment for namespace 2'
   */
  'ns2' => 
  array (
    's1' => 
    array (
      0 => 's11',
    ),
  ),
  /**
   * 'comment for namespace 3'
   */
  'ns3' => 'just text',
  /**
   * 'comment for namespace 4'
   */
  'ns4' => 'just text'
);

TEXT;
        return [
            ['string', [], "<?php\nreturn 'string';\n"],
            ['string', ['comment'], "<?php\nreturn 'string';\n"],
            [$array, [], "<?php\nreturn " . var_export($array, true) . ";\n"],
            [$array, $comments1, $expectedResult1],
            [$array, $comments2, $expectedResult2],
        ];
    }
}
