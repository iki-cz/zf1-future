<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Pdf
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Pdf */
require_once 'Zend/Pdf.php';

/**
 * @category   Zend
 * @package    Zend_Pdf
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Pdf
 */
class Zend_Pdf_ProcessingTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        date_default_timezone_set('GMT');
    }

    public function testCreate()
    {
        $pdf = new Zend_Pdf();

        // Add new page generated by Zend_Pdf object (page is attached to the specified the document)
        $pdf->pages[] = ($page1 = $pdf->newPage('A4'));

        // Add new page generated by Zend_Pdf_Page object (page is not attached to the document)
        $pdf->pages[] = ($page2 = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_LETTER_LANDSCAPE));

        // Create new font
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);

        // Apply font and draw text
        $page1->setFont($font, 36)
              ->setFillColor(Zend_Pdf_Color_Html::color('#9999cc'))
              ->drawText('Helvetica 36 text string', 60, 500);

        // Use font object for another page
        $page2->setFont($font, 24)
              ->drawText('Helvetica 24 text string', 60, 500);

        // Use another font
        $page2->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 32)
              ->drawText('Times-Roman 32 text string', 60, 450);

        // Draw rectangle
        $page2->setFillColor(new Zend_Pdf_Color_GrayScale(0.8))
              ->setLineColor(new Zend_Pdf_Color_GrayScale(0.2))
              ->setLineDashingPattern([3, 2, 3, 4], 1.6)
              ->drawRectangle(60, 400, 500, 350);

        // Draw rounded rectangle
        $page2->setFillColor(new Zend_Pdf_Color_GrayScale(0.9))
              ->setLineColor(new Zend_Pdf_Color_GrayScale(0.5))
              ->setLineDashingPattern(Zend_Pdf_Page::LINE_DASHING_SOLID)
              ->drawRoundedRectangle(425, 350, 475, 400, 20);

        // Draw circle
        $page2->setLineDashingPattern(Zend_Pdf_Page::LINE_DASHING_SOLID)
              ->setFillColor(new Zend_Pdf_Color_Rgb(1, 0, 0))
              ->drawCircle(85, 375, 25);

        // Draw sectors
        $page2->drawCircle(200, 375, 25, 2*M_PI/3, -M_PI/6)
              ->setFillColor(new Zend_Pdf_Color_Cmyk(1, 0, 0, 0))
              ->drawCircle(200, 375, 25, M_PI/6, 2*M_PI/3)
              ->setFillColor(new Zend_Pdf_Color_Rgb(1, 1, 0))
              ->drawCircle(200, 375, 25, -M_PI/6, M_PI/6);

        // Draw ellipse
        $page2->setFillColor(new Zend_Pdf_Color_Rgb(1, 0, 0))
              ->drawEllipse(250, 400, 400, 350)
              ->setFillColor(new Zend_Pdf_Color_Cmyk(1, 0, 0, 0))
              ->drawEllipse(250, 400, 400, 350, M_PI/6, 2*M_PI/3)
              ->setFillColor(new Zend_Pdf_Color_Rgb(1, 1, 0))
              ->drawEllipse(250, 400, 400, 350, -M_PI/6, M_PI/6);

        // Draw and fill polygon
        $page2->setFillColor(new Zend_Pdf_Color_Rgb(1, 0, 1));
        $x = [];
        $y = [];
        for ($count = 0; $count < 8; $count++) {
            $x[] = 140 + 25*cos(3*M_PI_4*$count);
            $y[] = 375 + 25*sin(3*M_PI_4*$count);
        }
        $page2->drawPolygon($x, $y,
                            Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE,
                            Zend_Pdf_Page::FILL_METHOD_EVEN_ODD);

        // Draw line
        $page2->setLineWidth(0.5)
              ->drawLine(60, 375, 500, 375);

        $pdf->save(dirname(__FILE__) . '/_files/output.pdf');
        unset($pdf);

        $pdf1 = Zend_Pdf::load(dirname(__FILE__) . '/_files/output.pdf');

        $this->assertTrue($pdf1 instanceof Zend_Pdf);
        unset($pdf1);

        unlink(dirname(__FILE__) . '/_files/output.pdf');
    }

    public function testModify()
    {
        $pdf = Zend_Pdf::load(dirname(__FILE__) . '/_files/pdfarchiving.pdf');

        // Reverse page order
        $pdf->pages = array_reverse($pdf->pages);

        // Mark page as modified
        foreach ($pdf->pages as $page){
            $page->saveGS();

            // Create new Style
            $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0.9))
                 ->setLineColor(new Zend_Pdf_Color_GrayScale(0.2))
                 ->setLineWidth(3)
                 ->setLineDashingPattern([3, 2, 3, 4], 1.6)
                 ->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 32);


            $page->rotate(0, 0, M_PI_2/3)
                 ->drawText('Modified by Zend Framework!', 150, 0)
                 ->restoreGS();
        }


        // Add new page generated by Zend_Pdf object (page is attached to the specified the document)
        $pdf->pages[] = ($page1 = $pdf->newPage('A4'));

        // Add new page generated by Zend_Pdf_Page object (page is not attached to the document)
        $pdf->pages[] = ($page2 = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_LETTER_LANDSCAPE));

        // Create new font
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);

        // Apply font and draw text
        $page1->setFont($font, 36)
              ->setFillColor(Zend_Pdf_Color_Html::color('#9999cc'))
              ->drawText('Helvetica 36 text string', 60, 500);

        // Use font object for another page
        $page2->setFont($font, 24)
              ->drawText('Helvetica 24 text string', 60, 500);

        // Use another font
        $page2->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 32)
              ->drawText('Times-Roman 32 text string', 60, 450);

        // Draw rectangle
        $page2->setFillColor(new Zend_Pdf_Color_GrayScale(0.8))
              ->setLineColor(new Zend_Pdf_Color_GrayScale(0.2))
              ->setLineDashingPattern([3, 2, 3, 4], 1.6)
              ->drawRectangle(60, 400, 500, 350);

        // Draw rounded rectangle
        $page2->setFillColor(new Zend_Pdf_Color_GrayScale(0.9))
              ->setLineColor(new Zend_Pdf_Color_GrayScale(0.5))
              ->setLineDashingPattern(Zend_Pdf_Page::LINE_DASHING_SOLID)
              ->drawRoundedRectangle(425, 350, 475, 400, 20);

        // Draw circle
        $page2->setLineDashingPattern(Zend_Pdf_Page::LINE_DASHING_SOLID)
              ->setFillColor(new Zend_Pdf_Color_Rgb(1, 0, 0))
              ->drawCircle(85, 375, 25);

        // Draw sectors
        $page2->drawCircle(200, 375, 25, 2*M_PI/3, -M_PI/6)
              ->setFillColor(new Zend_Pdf_Color_Cmyk(1, 0, 0, 0))
              ->drawCircle(200, 375, 25, M_PI/6, 2*M_PI/3)
              ->setFillColor(new Zend_Pdf_Color_Rgb(1, 1, 0))
              ->drawCircle(200, 375, 25, -M_PI/6, M_PI/6);

        // Draw ellipse
        $page2->setFillColor(new Zend_Pdf_Color_Rgb(1, 0, 0))
              ->drawEllipse(250, 400, 400, 350)
              ->setFillColor(new Zend_Pdf_Color_Cmyk(1, 0, 0, 0))
              ->drawEllipse(250, 400, 400, 350, M_PI/6, 2*M_PI/3)
              ->setFillColor(new Zend_Pdf_Color_Rgb(1, 1, 0))
              ->drawEllipse(250, 400, 400, 350, -M_PI/6, M_PI/6);

        // Draw and fill polygon
        $page2->setFillColor(new Zend_Pdf_Color_Rgb(1, 0, 1));
        $x = [];
        $y = [];
        for ($count = 0; $count < 8; $count++) {
            $x[] = 140 + 25*cos(3*M_PI_4*$count);
            $y[] = 375 + 25*sin(3*M_PI_4*$count);
        }
        $page2->drawPolygon($x, $y,
                            Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE,
                            Zend_Pdf_Page::FILL_METHOD_EVEN_ODD);

        // Draw line
        $page2->setLineWidth(0.5)
              ->drawLine(60, 375, 500, 375);

        $pdf->save(dirname(__FILE__) . '/_files/output.pdf');

        unset($pdf);

        $pdf1 = Zend_Pdf::load(dirname(__FILE__) . '/_files/output.pdf');

        $this->assertTrue($pdf1 instanceof Zend_Pdf);
        unset($pdf1);

        unlink(dirname(__FILE__) . '/_files/output.pdf');
    }

    public function testInfoProcessing()
    {
        $pdf = Zend_Pdf::load(dirname(__FILE__) . '/_files/pdfarchiving.pdf');

        $this->assertEquals($pdf->properties['Title'], 'PDF as a Standard for Archiving');
        $this->assertEquals($pdf->properties['Author'], 'Adobe Systems Incorporated');

        $metadata = $pdf->getMetadata();

        $metadataDOM = new DOMDocument();
        $metadataDOM->loadXML($metadata);

        $xpath = new DOMXPath($metadataDOM);
        $pdfPreffixNamespaceURI = $xpath->query('/rdf:RDF/rdf:Description')->item(0)->lookupNamespaceURI('pdf');
        $xpath->registerNamespace('pdf', $pdfPreffixNamespaceURI);

        $titleNodeset = $xpath->query('/rdf:RDF/rdf:Description/pdf:Title');
        $titleNode    = $titleNodeset->item(0);
        $this->assertEquals($titleNode->nodeValue, 'PDF as a Standard for Archiving');


        $pdf->properties['Title'] .= ' (modified)';
        $pdf->properties['New_Property'] = 'New property';

        $titleNode->nodeValue .= ' (modified using RDF data)';
        $pdf->setMetadata($metadataDOM->saveXML());

        $pdf->save(dirname(__FILE__) . '/_files/output.pdf');
        unset($pdf);


        $pdf1 = Zend_Pdf::load(dirname(__FILE__) . '/_files/output.pdf');
        $this->assertEquals($pdf1->properties['Title'], 'PDF as a Standard for Archiving (modified)');
        $this->assertEquals($pdf1->properties['Author'], 'Adobe Systems Incorporated');
        $this->assertEquals($pdf1->properties['New_Property'], 'New property');

        $metadataDOM1 = new DOMDocument();
        $metadataDOM1->loadXML($metadata);

        $xpath1 = new DOMXPath($metadataDOM);
        $pdfPreffixNamespaceURI1 = $xpath1->query('/rdf:RDF/rdf:Description')->item(0)->lookupNamespaceURI('pdf');
        $xpath1->registerNamespace('pdf', $pdfPreffixNamespaceURI1);

        $titleNodeset1 = $xpath->query('/rdf:RDF/rdf:Description/pdf:Title');
        $titleNode1    = $titleNodeset->item(0);
        $this->assertEquals($titleNode1->nodeValue, 'PDF as a Standard for Archiving (modified using RDF data)');
        unset($pdf1);

        unlink(dirname(__FILE__) . '/_files/output.pdf');
    }

    public function testPageCloning()
    {
        $pdf = Zend_Pdf::load(dirname(__FILE__) . '/_files/pdfarchiving.pdf');

        $srcPageCount = count($pdf->pages);

        try {
            $newPage = clone reset($pdf->pages);
        } catch (Zend_Pdf_Exception $e) {
            if (strpos($e->getMessage(), 'Cloning Zend_Pdf_Page object using \'clone\' keyword is not supported.') !== 0) {
                throw $e;
            }

            // Exception is thrown
        }

        $outputPageSet = [];
        foreach ($pdf->pages as $srcPage){
            $page = new Zend_Pdf_Page($srcPage);

            $outputPageSet[] = $srcPage;
            $outputPageSet[] = $page;

            $page->saveGS();

            // Create new Style
            $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0.9))
                 ->setLineColor(new Zend_Pdf_Color_GrayScale(0.2))
                 ->setLineWidth(3)
                 ->setLineDashingPattern([3, 2, 3, 4], 1.6)
                 ->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 32);


            $page->rotate(0, 0, M_PI_2/3);
            $page->drawText('Modified by Zend Framework!', 150, 0);
            $page->restoreGS();
        }


        // Add new page generated by Zend_Pdf object (page is attached to the specified the document)
        $pdf->pages = $outputPageSet;

        $pdf->save(dirname(__FILE__) . '/_files/output.pdf');

        unset($pdf);

        $pdf1 = Zend_Pdf::load(dirname(__FILE__) . '/_files/output.pdf');

        $this->assertTrue($pdf1 instanceof Zend_Pdf);
        $this->assertEquals($srcPageCount*2, count($pdf1->pages));
        unset($pdf1);

        unlink(dirname(__FILE__) . '/_files/output.pdf');
    }

    /**
     * @group ZF-3701
     */
    public function testZendPdfIsExtendableWithAccessToProperties()
    {
        $pdf = new ExtendedZendPdf();

        // Test accessing protected variables and their default content
        $this->assertEquals([], $pdf->_originalProperties);
        $this->assertEquals([], $pdf->_namedTargets);

        $pdfpage = new ExtendedZendPdfPage(Zend_Pdf_Page::SIZE_A4);
        // Test accessing protected variables and their default content
        $this->assertEquals(0, $pdfpage->_saveCount);
    }

    public function testLoadPdfWithUnknownEncodingInProperties()
    {
        $pdf = Zend_Pdf::load(__DIR__ . '/_files/Word-Export.pdf');
        // Changing a property is required to trigger the re-encoding of the existing properties
        $pdf->properties['Title'] = 'Test-Title';
        $pdf->render();

        $this->assertInstanceOf(Zend_Pdf::class, $pdf);
    }
}


class ExtendedZendPdf extends Zend_Pdf
{
    public function __get($name) {
        if(isset($this->$name)) {
            return $this->$name;
        }
    }
}
class ExtendedZendPdfPage extends Zend_Pdf_Page
{
    public function __get($name) {
        if(isset($this->$name)) {
            return $this->$name;
        }
    }
}
