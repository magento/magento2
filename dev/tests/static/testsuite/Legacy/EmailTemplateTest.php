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
 * @category    tests
 * @package     static
 * @subpackage  Legacy
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tests for obsolete directives in email templates
 */
class Legacy_EmailTemplateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $file
     * @dataProvider obsoleteDirectivesDataProvider
     */
    public function testObsoleteDirectives($file)
    {
        $suggestion = sprintf(Legacy_ObsoleteCodeTest::SUGGESTION_MESSAGE, '{{escapehtml}}');
        $this->assertNotRegExp(
            '/\{\{htmlescape.*?\}\}/i',
            file_get_contents($file),
            'Directive {{htmlescape}} is obsolete. ' . $suggestion
        );
    }

    public function obsoleteDirectivesDataProvider()
    {
        return Utility_Files::init()->getEmailTemplates();
    }
}
