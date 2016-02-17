<?php
namespace Pelago\Tests\Unit;

use Pelago\Emogrifier;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class EmogrifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    const LF = "\n";

    /**
     * @var string
     */
    private $html4TransitionalDocumentType = '';

    /**
     * @var string
     */
    private $xhtml1StrictDocumentType = '';

    /**
     * @var string
     */
    private $html5DocumentType = '<!DOCTYPE html>';

    /**
     * @var Emogrifier
     */
    private $subject = null;

    /**
     * Sets up the test case.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->html4TransitionalDocumentType = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" '
            . '"http://www.w3.org/TR/REC-html40/loose.dtd">';
        $this->xhtml1StrictDocumentType = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" '
            . '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

        $this->subject = new Emogrifier();
    }

    /**
     * @test
     *
     * @expectedException \BadMethodCallException
     */
    public function emogrifyForNoDataSetReturnsThrowsException()
    {
        $this->subject->emogrify();
    }

    /**
     * @test
     *
     * @expectedException \BadMethodCallException
     */
    public function emogrifyForEmptyHtmlAndEmptyCssThrowsException()
    {
        $this->subject->setHtml('');
        $this->subject->setCss('');

        $this->subject->emogrify();
    }

    /**
     * @test
     */
    public function emogrifyByDefaultEncodesUmlautsAsHtmlEntities()
    {
        $html = $this->html5DocumentType . '<html><p>Einen schönen Gruß!</p></html>';
        $this->subject->setHtml($html);

        $this->assertContains(
            'Einen sch&ouml;nen Gru&szlig;!',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyCanKeepEncodedUmlauts()
    {
        $this->subject->preserveEncoding = true;
        $encodedString = 'Küss die Hand, schöne Frau.';

        $html = $this->html5DocumentType . '<html><p>' . $encodedString . '</p></html>';
        $this->subject->setHtml($html);

        $this->assertContains(
            $encodedString,
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyForHtmlTagOnlyAndEmptyCssReturnsHtmlTagWithHtml4DocumentType()
    {
        $html = '<html></html>';
        $this->subject->setHtml($html);
        $this->subject->setCss('');

        $this->assertSame(
            $this->html4TransitionalDocumentType . self::LF . $html . self::LF,
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyForHtmlTagWithXhtml1StrictDocumentTypeKeepsDocumentType()
    {
        $html = $this->xhtml1StrictDocumentType . self::LF . '<html></html>' . self::LF;
        $this->subject->setHtml($html);
        $this->subject->setCss('');

        $this->assertSame(
            $html,
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyForHtmlTagWithXhtml5DocumentTypeKeepsDocumentType()
    {
        $html = $this->html5DocumentType . self::LF . '<html></html>' . self::LF;
        $this->subject->setHtml($html);
        $this->subject->setCss('');

        $this->assertSame(
            $html,
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyByDefaultRemovesWbrTag()
    {
        $html = $this->html5DocumentType . self::LF . '<html>foo<wbr/>bar</html>' . self::LF;
        $this->subject->setHtml($html);

        $this->assertContains(
            'foobar',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function addUnprocessableTagCausesTagToBeRemoved()
    {
        $this->subject->addUnprocessableHtmlTag('p');

        $html = $this->html5DocumentType . self::LF . '<html><p></p></html>' . self::LF;
        $this->subject->setHtml($html);

        $this->assertNotContains(
            '<p>',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function addUnprocessableTagNotRemovesTagWithContent()
    {
        $this->subject->addUnprocessableHtmlTag('p');

        $html = $this->html5DocumentType . self::LF . '<html><p>foobar</p></html>' . self::LF;
        $this->subject->setHtml($html);

        $this->assertContains(
            '<p>',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function removeUnprocessableHtmlTagCausesTagToStayAgain()
    {
        $this->subject->addUnprocessableHtmlTag('p');
        $this->subject->removeUnprocessableHtmlTag('p');

        $html = $this->html5DocumentType . self::LF . '<html><p>foo<br/><span>bar</span></p></html>' . self::LF;
        $this->subject->setHtml($html);

        $this->assertContains(
            '<p>',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyCanAddMatchingElementRuleOnHtmlElementFromCss()
    {
        $html = $this->html5DocumentType . self::LF . '<html></html>' . self::LF;
        $this->subject->setHtml($html);
        $styleRule = 'color: #000;';
        $this->subject->setCss('html {' . $styleRule . '}');

        $this->assertContains(
            '<html style="' . $styleRule . '">',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyNotAddsNotMatchingElementRuleOnHtmlElementFromCss()
    {
        $html = $this->html5DocumentType . self::LF . '<html></html>' . self::LF;
        $this->subject->setHtml($html);
        $this->subject->setCss('p {color:#000;}');

        $this->assertContains(
            '<html>',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyCanMatchTwoElements()
    {
        $html = $this->html5DocumentType . self::LF . '<html><p></p><p></p></html>' . self::LF;
        $this->subject->setHtml($html);
        $styleRule = 'color: #000;';
        $this->subject->setCss('p {' . $styleRule . '}');

        $this->assertSame(
            2,
            substr_count($this->subject->emogrify(), '<p style="' . $styleRule . '">')
        );
    }

    /**
     * @test
     */
    public function emogrifyCanAssignTwoStyleRulesFromSameMatcherToElement()
    {
        $html = $this->html5DocumentType . self::LF . '<html><p></p></html>' . self::LF;
        $this->subject->setHtml($html);
        $styleRulesIn = 'color:#000; text-align:left;';
        $styleRulesOut = 'color: #000; text-align: left;';
        $this->subject->setCss('p {' . $styleRulesIn . '}');

        $this->assertContains(
            '<p style="' . $styleRulesOut . '">',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyCanMatchAttributeOnlySelector()
    {
        $html = $this->html5DocumentType . self::LF . '<html><p hidden="hidden"></p></html>' . self::LF;
        $this->subject->setHtml($html);
        $this->subject->setCss('[hidden] { color:red; }');

        $this->assertContains(
            '<p hidden="hidden" style="color: red;">',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyCanAssignStyleRulesFromTwoIdenticalMatchersToElement()
    {
        $html = $this->html5DocumentType . self::LF . '<html><p></p></html>' . self::LF;
        $this->subject->setHtml($html);
        $styleRule1 = 'color: #000;';
        $styleRule2 = 'text-align: left;';
        $this->subject->setCss('p {' . $styleRule1 . '}  p {' . $styleRule2 . '}');

        $this->assertContains(
            '<p style="' . $styleRule1 . ' ' . $styleRule2 . '">',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyCanAssignStyleRulesFromTwoDifferentMatchersToElement()
    {
        $html = $this->html5DocumentType . self::LF . '<html><p class="x"></p></html>' . self::LF;
        $this->subject->setHtml($html);
        $styleRule1 = 'color: #000;';
        $styleRule2 = 'text-align: left;';
        $this->subject->setCss('p {' . $styleRule1 . '}  .x {' . $styleRule2 . '}');

        $this->assertContains(
            '<p class="x" style="' . $styleRule1 . ' ' . $styleRule2 . '">',
            $this->subject->emogrify()
        );
    }

    /**
     * Data provide for selectors.
     *
     * @return array[]
     */
    public function selectorDataProvider()
    {
        $styleRule = 'color: red;';
        $styleAttribute = 'style="' . $styleRule . '"';

        return array(
            'universal selector HTML'
                => array('* {' . $styleRule . '} ', '#<html id="html" ' . $styleAttribute . '>#'),
            'universal selector BODY'
                => array('* {' . $styleRule . '} ', '#<body ' . $styleAttribute . '>#'),
            'universal selector P'
                => array('* {' . $styleRule . '} ', '#<p[^>]*' . $styleAttribute . '>#'),
            'type selector matches first P'
                => array('p {' . $styleRule . '} ', '#<p class="p-1" ' . $styleAttribute . '>#'),
            'type selector matches second P'
                => array('p {' . $styleRule . '} ', '#<p class="p-2" ' . $styleAttribute . '>#'),
            'descendant selector P SPAN'
                => array('p span {' . $styleRule . '} ', '#<span ' . $styleAttribute . '>#'),
            'descendant selector BODY SPAN'
                => array('body span {' . $styleRule . '} ', '#<span ' . $styleAttribute . '>#'),
            'child selector P > SPAN matches direct child'
                => array('p > span {' . $styleRule . '} ', '#<span ' . $styleAttribute . '>#'),
            'child selector BODY > SPAN not matches grandchild'
                => array('body > span {' . $styleRule . '} ', '#<span>#'),
            'adjacent selector P + P not matches first P' => array('p + p {' . $styleRule . '} ', '#<p class="p-1">#'),
            'adjacent selector P + P matches second P'
                => array('p + p {' . $styleRule . '} ', '#<p class="p-2" style="' . $styleRule . '">#'),
            'adjacent selector P + P matches third P'
                => array('p + p {' . $styleRule . '} ', '#<p class="p-3" style="' . $styleRule . '">#'),
            'ID selector #HTML' => array('#html {' . $styleRule . '} ', '#<html id="html" ' . $styleAttribute . '>#'),
            'type and ID selector HTML#HTML'
                => array('html#html {' . $styleRule . '} ', '#<html id="html" ' . $styleAttribute . '>#'),
            'class selector .P-1' => array('.p-1 {' . $styleRule . '} ', '#<p class="p-1" ' . $styleAttribute . '>#'),
            'type and class selector P.P-1'
                => array('p.p-1 {' . $styleRule . '} ', '#<p class="p-1" ' . $styleAttribute . '>#'),
            'attribute presence selector SPAN[title] matches element with matching attribute'
                => array('span[title] {' . $styleRule . '} ', '#<span title="bonjour" ' . $styleAttribute . '>#'),
            'attribute presence selector SPAN[title] not matches element without any attributes'
                => array('span[title] {' . $styleRule . '} ', '#<span>#'),
            'attribute value selector SPAN[title] matches element with matching attribute value' => array(
                'span[title="bonjour"] {' . $styleRule . '} ', '#<span title="bonjour" ' . $styleAttribute . '>#'
            ),
            'attribute value selector SPAN[title] not matches element with other attribute value'
                => array('span[title="bonjour"] {' . $styleRule . '} ', '#<span title="buenas dias">#'),
            'attribute value selector SPAN[title] not matches element without any attributes'
                => array('span[title="bonjour"] {' . $styleRule . '} ', '#<span>#'),
        );
    }

    /**
     * @test
     *
     * @param string $css the complete CSS
     * @param string $containedHtml regular expression for the the HTML that needs to be contained in the merged HTML
     *
     * @dataProvider selectorDataProvider
     */
    public function emogrifierMatchesSelectors($css, $containedHtml)
    {
        $html = $this->html5DocumentType . self::LF .
            '<html id="html">' .
            '  <body>' .
            '    <p class="p-1"><span>some text</span></p>' .
            '    <p class="p-2"><span title="bonjour">some</span> text</p>' .
            '    <p class="p-3"><span title="buenas dias">some</span> more text</p>' .
            '  </body>' .
            '</html>';

        $this->subject->setHtml($html);
        $this->subject->setCss($css);

        $this->assertRegExp(
            $containedHtml,
            $this->subject->emogrify()
        );
    }

    /**
     * Data provider for emogrifyDropsWhitespaceFromCssDeclarations.
     *
     * @return array[]
     */
    public function cssDeclarationWhitespaceDroppingDataProvider()
    {
        return array(
            'no whitespace, trailing semicolon' => array('color:#000;', 'color: #000;'),
            'no whitespace, no trailing semicolon' => array('color:#000', 'color: #000;'),
            'space after colon, no trailing semicolon' => array('color: #000', 'color: #000;'),
            'space before colon, no trailing semicolon' => array('color :#000', 'color: #000;'),
            'space before property name, no trailing semicolon' => array(' color:#000', 'color: #000;'),
            'space before trailing semicolon' => array(' color:#000 ;', 'color: #000;'),
            'space after trailing semicolon' => array(' color:#000; ', 'color: #000;'),
            'space after property value, no trailing semicolon' => array(' color:#000; ', 'color: #000;'),
        );
    }

    /**
     * @test
     *
     * @param string $cssDeclarationBlock the CSS declaration block (without the curly braces)
     * @param string $expectedStyleAttributeContent the expected value of the style attribute
     *
     * @dataProvider cssDeclarationWhitespaceDroppingDataProvider
     */
    public function emogrifyDropsLeadingAndTrailingWhitespaceFromCssDeclarations(
        $cssDeclarationBlock,
        $expectedStyleAttributeContent
    ) {
        $html = $this->html5DocumentType . self::LF . '<html></html>';
        $css = 'html {' . $cssDeclarationBlock . '}';

        $this->subject->setHtml($html);
        $this->subject->setCss($css);

        $this->assertContains(
            'html style="' . $expectedStyleAttributeContent . '">',
            $this->subject->emogrify()
        );
    }

    /**
     * Data provider for emogrifyFormatsCssDeclarations.
     *
     * @return array[]
     */
    public function formattedCssDeclarationDataProvider()
    {
        return array(
            'one declaration' => array('color: #000;', 'color: #000;'),
            'one declaration with dash in property name' => array('font-weight: bold;', 'font-weight: bold;'),
            'one declaration with space in property value' => array('margin: 0 4px;', 'margin: 0 4px;'),
            'two declarations separated by semicolon' => array('color: #000;width: 3px;', 'color: #000; width: 3px;'),
            'two declarations separated by semicolon and space'
                => array('color: #000; width: 3px;', 'color: #000; width: 3px;'),
            'two declaration separated by semicolon and Linefeed' => array(
                'color: #000;' . self::LF . 'width: 3px;', 'color: #000; width: 3px;'
            ),
        );
    }

    /**
     * @test
     *
     * @param string $cssDeclarationBlock the CSS declaration block (without the curly braces)
     * @param string $expectedStyleAttributeContent the expected value of the style attribute
     *
     * @dataProvider formattedCssDeclarationDataProvider
     */
    public function emogrifyFormatsCssDeclarations($cssDeclarationBlock, $expectedStyleAttributeContent)
    {
        $html = $this->html5DocumentType . self::LF . '<html></html>';
        $css = 'html {' . $cssDeclarationBlock . '}';

        $this->subject->setHtml($html);
        $this->subject->setCss($css);

        $this->assertContains(
            'html style="' . $expectedStyleAttributeContent . '">',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyKeepsExistingStyleAttributes()
    {
        $styleAttribute = 'style="color: #ccc;"';
        $html = $this->html5DocumentType . self::LF . '<html ' . $styleAttribute . '></html>';
        $this->subject->setHtml($html);

        $this->assertContains(
            $styleAttribute,
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyAddsCssAfterExistingStyle()
    {
        $styleAttributeValue = 'color: #ccc;';
        $html = $this->html5DocumentType . self::LF . '<html style="' . $styleAttributeValue . '"></html>';
        $this->subject->setHtml($html);

        $cssDeclarations = 'margin: 0 2px;';
        $css = 'html {' . $cssDeclarations . '}';
        $this->subject->setCss($css);

        $this->assertContains(
            'style="' . $styleAttributeValue . ' ' . $cssDeclarations . '"',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyCanMatchMinifiedCss()
    {
        $html = $this->html5DocumentType . self::LF . '<html><p></p></html>' . self::LF;
        $this->subject->setHtml($html);
        $this->subject->setCss('p{color:blue;}html{color:red;}');

        $this->assertContains(
            '<html style="color: red;">',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyLowercasesAttributeNamesFromStyleAttributes()
    {
        $html = $this->html5DocumentType . self::LF . '<html style="COLOR:#ccc;"></html>';
        $this->subject->setHtml($html);

        $this->assertContains(
            'style="color: #ccc;"',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyLowerCasesAttributeNames()
    {
        $html = $this->html5DocumentType . self::LF . '<html></html>';
        $this->subject->setHtml($html);
        $cssIn = 'html {mArGiN:0 2pX;}';
        $cssOut = 'margin: 0 2pX;';
        $this->subject->setCss($cssIn);

        $this->assertContains(
            'style="' . $cssOut . '"',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyPreservesCaseForAttributeValuesFromPassedInCss()
    {
        $css = "content: 'Hello World';";
        $html = $this->html5DocumentType . self::LF . '<html><body><p>target</p></body></html>';
        $this->subject->setHtml($html);
        $this->subject->setCss('p {' . $css . '}');

        $this->assertContains(
            '<p style="' . $css . '">target</p>',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyPreservesCaseForAttributeValuesFromParsedStyleBlock()
    {
        $css = "content: 'Hello World';";
        $html = $this->html5DocumentType . self::LF . '<html><head><style>p {'
            . $css . '}</style></head><body><p>target</p></body></html>';
        $this->subject->setHtml($html);

        $this->assertContains(
            '<p style="' . $css . '">target</p>',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyRemovesStyleNodes()
    {
        $html = $this->html5DocumentType . self::LF . '<html><style type="text/css"></style></html>';
        $this->subject->setHtml($html);

        $this->assertNotContains(
            '<style>',
            $this->subject->emogrify()
        );
    }

    /**
     * Data provider for things that should be left out when applying the CSS.
     *
     * @return array[]
     */
    public function unneededCssThingsDataProvider()
    {
        return array(
            'CSS comments with one asterisk' => array('p {color: #000;/* black */}', 'black'),
            'CSS comments with two asterisks' => array('p {color: #000;/** black */}', 'black'),
            '@import directive' => array('@import "foo.css";', '@import'),
            'style in "aural" media type rule' => array('@media aural {p {color: #000;}}', '#000'),
            'style in "braille" media type rule' => array('@media braille {p {color: #000;}}', '#000'),
            'style in "embossed" media type rule' => array('@media embossed {p {color: #000;}}', '#000'),
            'style in "handheld" media type rule' => array('@media handheld {p {color: #000;}}', '#000'),
            'style in "print" media type rule' => array('@media print {p {color: #000;}}', '#000'),
            'style in "projection" media type rule' => array('@media projection {p {color: #000;}}', '#000'),
            'style in "speech" media type rule' => array('@media speech {p {color: #000;}}', '#000'),
            'style in "tty" media type rule' => array('@media tty {p {color: #000;}}', '#000'),
            'style in "tv" media type rule' => array('@media tv {p {color: #000;}}', '#000'),
        );
    }

    /**
     * @test
     *
     * @param string $css
     * @param string $markerNotExpectedInHtml
     *
     * @dataProvider unneededCssThingsDataProvider
     */
    public function emogrifyFiltersUnneededCssThings($css, $markerNotExpectedInHtml)
    {
        $html = $this->html5DocumentType . self::LF . '<html><p>foo</p></html>';
        $this->subject->setHtml($html);
        $this->subject->setCss($css);

        $this->assertNotContains(
            $markerNotExpectedInHtml,
            $this->subject->emogrify()
        );
    }

    /**
     * Data provider for media rules.
     *
     * @return array[]
     */
    public function mediaRulesDataProvider()
    {
        return array(
            'style in "only all" media type rule' => array('@media only all {p {color: #000;}}'),
            'style in "only screen" media type rule' => array('@media only screen {p {color: #000;}}'),
            'style in media type rule' => array('@media {p {color: #000;}}'),
            'style in "screen" media type rule' => array('@media screen {p {color: #000;}}'),
            'style in "all" media type rule' => array('@media all {p {color: #000;}}'),
        );
    }

    /**
     * @test
     *
     * @param string $css
     *
     * @dataProvider mediaRulesDataProvider
     */
    public function emogrifyKeepsMediaRules($css)
    {
          $html = $this->html5DocumentType . self::LF . '<html><p>foo</p></html>';
          $this->subject->setHtml($html);
          $this->subject->setCss($css);

          $this->assertContains(
              $css,
              $this->subject->emogrify()
          );
    }

    /**
     * @test
     */
    public function emogrifyAddsMissingHeadElement()
    {
        $html = $this->html5DocumentType . self::LF . '<html></html>';
        $this->subject->setHtml($html);
        $this->subject->setCss('@media all { html {} }');

        $this->assertContains(
            '<head>',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyKeepExistingHeadElementContent()
    {
        $html = $this->html5DocumentType . self::LF . '<html><head><!-- original content --></head></html>';
        $this->subject->setHtml($html);
        $this->subject->setCss('@media all { html {} }');

        $this->assertContains(
            '<!-- original content -->',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyKeepExistingHeadElementAddStyleElement()
    {
        $html = $this->html5DocumentType . self::LF . '<html><head><!-- original content --></head></html>';
        $this->subject->setHtml($html);
        $this->subject->setCss('@media all { html {} }');

        $this->assertContains(
            '<style type="text/css">',
            $this->subject->emogrify()
        );
    }

    /**
     * Valid media query which need to be preserved
     *
     * @return array[]
     */
    public function validMediaPreserveDataProvider()
    {
        return array(
            'style in "only screen and size" media type rule' => array(
                '@media only screen and (min-device-width: 320px) and (max-device-width: 480px) '
                    . '{ h1 { color:red; } }'
            ),
            'style in "screen size" media type rule' => array(
                '@media screen and (min-device-width: 320px) and (max-device-width: 480px) '
                    . '{ h1 { color:red; } }'
            ),
            'style in "only screen and screen size" media type rule' => array(
                '@media only screen and (min-device-width: 320px) and (max-device-width: 480px) '
                    . '{ h1 { color:red; } }'
            ),
            'style in "all and screen size" media type rule' => array(
                '@media all and (min-device-width: 320px) and (max-device-width: 480px) '
                    . '{ h1 { color:red; } }'
            ),
            'style in "only all and" media type rule' => array(
                '@media only all and (min-device-width: 320px) and (max-device-width: 480px) '
                    . '{ h1 { color:red; } }'
            ),
            'style in "all" media type rule' => array('@media all {p {color: #000;}}'),
            'style in "only screen" media type rule' => array('@media only screen { h1 { color:red; } }'),
            'style in "only all" media type rule' => array('@media only all { h1 { color:red; } }'),
            'style in "screen" media type rule' => array('@media screen { h1 { color:red; } }'),
            'style in media type rule without specification' => array('@media { h1 { color:red; } }'),
        );
    }

    /**
     * @test
     *
     * @param string $css
     *
     * @dataProvider validMediaPreserveDataProvider
     */
    public function emogrifyWithValidMediaQueryContainsInnerCss($css)
    {
        $html = $this->html5DocumentType . PHP_EOL . '<html><h1></h1></html>';
        $this->subject->setHtml($html);
        $this->subject->setCss($css);

        $this->assertContains(
            $css,
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     *
     * @param string $css
     *
     * @dataProvider validMediaPreserveDataProvider
     */
    public function emogrifyForHtmlWithValidMediaQueryContainsInnerCss($css)
    {
        $html = $this->html5DocumentType . PHP_EOL . '<html><style type="text/css">' . $css
            . '</style><h1></h1></html>';
        $this->subject->setHtml($html);

        $this->assertContains(
            $css,
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     *
     * @param string $css
     *
     * @dataProvider validMediaPreserveDataProvider
     */
    public function emogrifyWithValidMediaQueryNotContainsInlineCss($css)
    {
        $html = $this->html5DocumentType . PHP_EOL . '<html><h1></h1></html>';
        $this->subject->setHtml($html);
        $this->subject->setCss($css);

        $this->assertNotContains(
            'style="color:red"',
            $this->subject->emogrify()
        );
    }

    /**
     * Invalid media query which need to be strip
     *
     * @return array[]
     */
    public function invalidMediaPreserveDataProvider()
    {
        return array(
            'style in "braille" type rule' => array('@media braille { h1 { color:red; } }'),
            'style in "embossed" type rule' => array('@media embossed { h1 { color:red; } }'),
            'style in "handheld" type rule' => array('@media handheld { h1 { color:red; } }'),
            'style in "print" type rule' => array('@media print { h1 { color:red; } }'),
            'style in "projection" type rule' => array('@media projection { h1 { color:red; } }'),
            'style in "speech" type rule' => array('@media speech { h1 { color:red; } }'),
            'style in "tty" type rule' => array('@media tty { h1 { color:red; } }'),
            'style in "tv" type rule' => array('@media tv { h1 { color:red; } }'),
        );
    }

    /**
     * @test
     *
     * @param string $css
     *
     * @dataProvider invalidMediaPreserveDataProvider
     */
    public function emogrifyWithInvalidMediaQueryaNotContainsInnerCss($css)
    {
        $html = $this->html5DocumentType . PHP_EOL . '<html><h1></h1></html>';
        $this->subject->setHtml($html);
        $this->subject->setCss($css);

        $this->assertNotContains(
            $css,
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     *
     * @param string $css
     *
     * @dataProvider invalidMediaPreserveDataProvider
     */
    public function emogrifyWithInValidMediaQueryNotContainsInlineCss($css)
    {
        $html = $this->html5DocumentType . PHP_EOL . '<html><h1></h1></html>';
        $this->subject->setHtml($html);
        $this->subject->setCss($css);

        $this->assertNotContains(
            'style="color: red"',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     *
     * @param string $css
     *
     * @dataProvider invalidMediaPreserveDataProvider
     */
    public function emogrifyFromHtmlWithInValidMediaQueryNotContainsInnerCss($css)
    {
        $html = $this->html5DocumentType . PHP_EOL . '<html><style type="text/css">' . $css
            . '</style><h1></h1></html>';
        $this->subject->setHtml($html);

        $this->assertNotContains(
            $css,
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     *
     * @param string $css
     *
     * @dataProvider invalidMediaPreserveDataProvider
     */
    public function emogrifyFromHtmlWithInValidMediaQueryNotContainsInlineCss($css)
    {
        $html = $this->html5DocumentType . PHP_EOL . '<html><style type="text/css">' . $css
            . '</style><h1></h1></html>';
        $this->subject->setHtml($html);

        $this->assertNotContains(
            'style="color: red"',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyAppliesCssFromStyleNodes()
    {
        $styleAttributeValue = 'color: #ccc;';
        $html = $this->html5DocumentType . self::LF .
        '<html><style type="text/css">html {' . $styleAttributeValue . '}</style></html>';
        $this->subject->setHtml($html);

        $this->assertContains(
            '<html style="' . $styleAttributeValue . '">',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyWhenDisabledNotAppliesCssFromStyleBlocks()
    {
        $styleAttributeValue = 'color: #ccc;';
        $html = $this->html5DocumentType . self::LF .
            '<html><style type="text/css">html {' . $styleAttributeValue . '}</style></html>';
        $this->subject->setHtml($html);
        $this->subject->disableStyleBlocksParsing();

        $this->assertNotContains(
            '<html style="' . $styleAttributeValue . '">',
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyWhenStyleBlocksParsingDisabledKeepInlineStyles()
    {
        $styleAttributeValue = 'text-align: center;';
        $html = $this->html5DocumentType . self::LF .
            '<html><head><style type="text/css">p { color: #ccc; }</style></head>'
                . '<body><p style="' . $styleAttributeValue . '">paragraph</p></body></html>';
        $expected = '<p style="' . $styleAttributeValue . '">';
        $this->subject->setHtml($html);
        $this->subject->disableStyleBlocksParsing();

        $this->assertContains(
            $expected,
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyWhenDisabledNotAppliesCssFromInlineStyles()
    {
        $styleAttributeValue = 'color: #ccc;';
        $html = $this->html5DocumentType . self::LF .
            '<html style="' . $styleAttributeValue . '"></html>';
        $expected = '<html></html>';
        $this->subject->setHtml($html);
        $this->subject->disableInlineStyleAttributesParsing();

        $this->assertContains(
            $expected,
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyWhenInlineStyleAttributesParsingDisabledKeepStyleBlockStyles()
    {
        $styleAttributeValue = 'color: #ccc;';
        $html = $this->html5DocumentType . self::LF .
            '<html><head><style type="text/css">p { ' . $styleAttributeValue . ' }</style></head>'
                . '<body><p style="text-align: center;">paragraph</p></body></html>';
        $expected = '<p style="' . $styleAttributeValue . '">';
        $this->subject->setHtml($html);
        $this->subject->disableInlineStyleAttributesParsing();

        $this->assertContains(
            $expected,
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyAppliesCssWithUpperCaseSelector()
    {
        $html = $this->html5DocumentType . self::LF .
            '<html><style type="text/css">P { color:#ccc; }</style><body><p>paragraph</p></body></html>';
        $expected = '<p style="color: #ccc;">';
        $this->subject->setHtml($html);

        $this->assertContains(
            $expected,
            $this->subject->emogrify()
        );
    }

    /**
     * Emogrify was handling case differently for passed in CSS vs CSS parsed from style blocks.
     * @test
     */
    public function emogrifyAppliesCssWithMixedCaseAttributesInStyleBlock()
    {
        $html = $this->html5DocumentType . self::LF .
            '<html><head><style>#topWrap p {padding-bottom: 1px;PADDING-TOP: 0;}</style></head>'
                . '<body><div id="topWrap"><p style="text-align: center;">some content</p></div></body></html>';
        $expected = '<p style="text-align: center; padding-bottom: 1px; padding-top: 0;">';
        $this->subject->setHtml($html);

        $this->assertContains(
            $expected,
            $this->subject->emogrify()
        );
    }

    /**
     * Passed in CSS sets the order, but style block CSS overrides values.
     * @test
     */
    public function emogrifyMergesCssWithMixedCaseAttribute()
    {
        $css = 'p { margin: 0; padding-TOP: 0; PADDING-bottom: 1PX;}';
        $html = $this->html5DocumentType . self::LF .
            '<html><head><style>#topWrap p {padding-bottom: 3px;PADDING-TOP: 1px;}</style></head>'
                . '<body><div id="topWrap"><p style="text-align: center;">some content</p></div></body></html>';
        $expected = '<p style="text-align: center; margin: 0; padding-top: 1px; padding-bottom: 3px;">';
        $this->subject->setHtml($html);
        $this->subject->setCss($css);

        $this->assertContains(
            $expected,
            $this->subject->emogrify()
        );
    }

    /**
     * @test
     */
    public function emogrifyMergesCssWithMixedUnits()
    {
        $css = 'p { margin: 1px; padding-bottom:0;}';
        $html = $this->html5DocumentType . self::LF .
            '<html><head><style>#topWrap p {margin:0;padding-bottom: 1px;}</style></head>'
                . '<body><div id="topWrap"><p style="text-align: center;">some content</p></div></body></html>';
        $expected = '<p style="text-align: center; margin: 0; padding-bottom: 1px;">';
        $this->subject->setHtml($html);
        $this->subject->setCss($css);

        $this->assertContains(
            $expected,
            $this->subject->emogrify()
        );
    }
}
