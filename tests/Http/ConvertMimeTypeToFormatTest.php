<?php


namespace Atom\Framework\Test\Http;

use Atom\Framework\Http\ConvertMimeTypeToFormat;
use PHPUnit\Framework\TestCase;

class ConvertMimeTypeToFormatTest extends TestCase
{

    public function testGetFormat()
    {
        $converter = new class {
            use ConvertMimeTypeToFormat;

            public function convert(string $mime)
            {
                return $this->getFormatOfMime($mime);
            }
        };
        $this->assertNull($converter->convert("foo"));
        $this->assertEquals("json", $converter->convert("application/json"));
        $this->assertEquals("html", $converter->convert("text/html"));
        $this->assertEquals("pptx", $converter->convert(
            "application/vnd.openxmlformats-officedocument.presentationml.presentation"
        ));
    }
}
