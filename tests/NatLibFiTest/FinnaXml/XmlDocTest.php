<?php

/**
 * XML Handling Test Class
 *
 * PHP version 8
 *
 * Copyright (C) 2026 University of Helsinki, National library of Finland.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category FinnaXml
 * @package  Tests
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  https://opensource.org/license/mit The MIT License
 * @link     https://github.com/NatLibFi/RecordManager
 */

namespace NatLibFiTest\FinnaXml;

use FinnaXml\Notation;
use FinnaXml\XmlDoc;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * XML Handling Test Class
 *
 * @category FinnaXml
 * @package  Tests
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  https://opensource.org/license/mit The MIT License
 * @link     https://github.com/NatLibFi/RecordManager
 */
class XmlDocTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test XML creation with existing namespaces.
     *
     * @return void
     */
    public function testXmlWithNs(): void
    {
        $xmlStr = $this->getFixture('xml-with-ns.xml');
        $xml = new XmlDoc();
        $xml->parse($xmlStr);
        $this->assertXmlStringEqualsXmlString(
            $xmlStr,
            $xml->toXML(2)
        );
    }

    /**
     * Test creating XML without namespaces.
     *
     * @return void
     */
    public function testXmlWithoutNs(): void
    {
        $xmlStr = $this->getFixture('xml-without-ns.xml');
        $xml = new XmlDoc();
        $xml->parse($xmlStr);
        $this->assertXmlStringEqualsXmlString(
            $this->getFixture('xml-with-only-xsi-ns.xml'),
            $xml->toXML(2)
        );
    }

    /**
     * Test adding of namespace when creating XML.
     *
     * @return void
     */
    public function stestXmlAddNs(): void
    {
        $xmlStr = $this->getFixture('xml-without-ns.xml');
        $xml = new XmlDoc();
        $xml->parse($xmlStr);
        $xml->setDefaultNamespace('http://www.lido-schema.org', 'lido');
        $this->assertXmlStringEqualsXmlString(
            $this->getFixture('xml-with-ns.xml'),
            $xml->toXML(2)
        );
    }

    /**
     * Test import/export.
     *
     * @return void
     */
    public function testImportExport(): void
    {
        $xmlStr = $this->getFixture('xml-with-multiple-ns.xml');
        $xml = new XmlDoc();
        $xml->parse($xmlStr);
        $xml2 = new XmlDoc();
        $xml2->import($xml->export());
        $this->assertXmlStringEqualsXmlString(
            $xmlStr,
            $xml2->toXML(2)
        );
    }

    /**
     * Test import with invalid data.
     *
     * @return void
     */
    public function testInvalidImportFormat(): void
    {
        $xml = new XmlDoc();
        $this->expectExceptionMessage('Invalid parsed document format');
        $xml->import([]);
    }

    /**
     * Test export without data.
     *
     * @return void
     */
    public function testInvalidExport(): void
    {
        $xml = new XmlDoc();
        $this->expectExceptionMessage('No parsed document available');
        $xml->export();
    }

    /**
     * Data provider for testReading.
     *
     * @return \Iterator
     */
    public static function readingProvider(): \Iterator
    {
        yield 'no default ns' => [
            null,
            '{http://www.lido-schema.org}lido/{http://www.lido-schema.org}lidoRecID',
            '{http://www.lido-schema.org}type',
        ];

        yield 'no default ns, array query' => [
            null,
            ['http://www.lido-schema.org lido', 'http://www.lido-schema.org lidoRecID'],
            '{http://www.lido-schema.org}type',
        ];

        yield 'default ns' => [
            'http://www.lido-schema.org',
            '{http://www.lido-schema.org}lido/{http://www.lido-schema.org}lidoRecID',
            '{http://www.lido-schema.org}type',
        ];

        yield 'default ns, simple path' => [
            'http://www.lido-schema.org',
            'lido/lidoRecID',
            'type',
        ];
    }

    /**
     * Test reading.
     *
     * @param ?string      $defaultNs Default namespace
     * @param string|array $path      Path
     * @param string       $attr      Attribute name
     *
     * @return void
     */
    #[DataProvider('readingProvider')]
    public function testReading(?string $defaultNs, string|array $path, string $attr): void
    {
        $xmlStr = $this->getFixture('xml-with-ns.xml');
        $xml = new XmlDoc();
        $xml->parse($xmlStr);

        $xml->setDefaultNamespace($defaultNs);
        $recId = $xml->all(path: $path);
        $this->assertIsArray($recId);
        $this->assertCount(1, $recId);
        $this->assertSame('12345', $xml->value($recId[0]));
        $this->assertSame('Test', $xml->attr($recId[0], $attr));
    }

    /**
     * Test reading values.
     *
     * @param ?string      $defaultNs Default namespace
     * @param string|array $path      Path
     * @param string       $attr      Attribute name
     *
     * @return void
     */
    #[DataProvider('readingProvider')]
    public function testReadingValues(?string $defaultNs, string|array $path, string $attr): void
    {
        $xmlStr = $this->getFixture('xml-with-ns.xml');
        $xml = new XmlDoc();
        $xml->parse($xmlStr);

        $xml->setDefaultNamespace($defaultNs);
        $recId = $xml->allValues(path: $path);
        $this->assertIsArray($recId);
        $this->assertCount(1, $recId);
        $this->assertSame('12345', $recId[0]);
    }

    /**
     * Data provider for testReadingAllValues.
     *
     * @return \Iterator
     */
    public static function readingAllValuesProvider(): \Iterator
    {
        yield 'ns' => ['xml-with-ns.xml'];
        yield 'no ns' => ['xml-without-ns.xml'];
    }

    /**
     * Test reading all values.
     *
     * @param string $fixture XML fixture
     *
     * @return void
     */
    #[DataProvider('readingAllValuesProvider')]
    public function testReadingAllValues(string $fixture): void
    {
        $xmlStr = $this->getFixture($fixture);
        $xml = new XmlDoc();
        $xml->parse($xmlStr);
        $xml->setDefaultNamespace('http://www.lido-schema.org');
        $objectIdentificationWraps = $xml->all(path: 'lido/descriptiveMetadata/objectIdentificationWrap');
        $this->assertSame(
            [
                'Kitchen tool',
                'Scissors',
                'Sakset',
                'Keittiövälineet',
            ],
            $xml->allValues($objectIdentificationWraps[0], 'titleWrap/titleSet/appellationValue')
        );

        $objectIdentificationWrap = $xml->first(path: 'lido/descriptiveMetadata/objectIdentificationWrap');
        $this->assertSame(
            [
                'Kitchen tool',
                'Scissors',
                'Sakset',
                'Keittiövälineet',
            ],
            $xml->allValues($objectIdentificationWrap, 'titleWrap/titleSet/appellationValue')
        );
        $this->assertNull($xml->attr($objectIdentificationWrap, 'foo'));

        $this->assertSame([], $xml->allValues(path: 'foo'));
    }

    /**
     * Test mixed content.
     *
     * @return void
     */
    public function testMixedContent(): void
    {
        $xmlStr = $this->getFixture('mixed-content.xml');
        $xml = new XmlDoc();
        $xml->parse($xmlStr);
        $xml->setDefaultNamespace('http://www.lido-schema.org', 'lido');
        $this->assertSame(
            'record-id',
            $xml->firstValue(path: 'lido/lidoRecID')
        );
        $this->assertSame(
            'root data',
            $xml->value($xml->root())
        );
        $this->assertSame(
            'lido',
            $xml->firstValue(path: '')
        );

        $this->assertSame(
            $this->getFixture('mixed-content-min.xml'),
            $xml->toXML(trim: true)
        );
    }

    /**
     * Test empty elements.
     *
     * @return void
     */
    public function testEmptyElements(): void
    {
        $xmlStr = $this->getFixture('empty-elements.xml');
        $xml = new XmlDoc();
        $xml->parse($xmlStr);
        $xml->setDefaultNamespace('http://www.lido-schema.org');
        $this->assertCount(
            3,
            $xml->all(path: 'lido/descriptiveMetadata/eventWrap/eventSet/event')
        );
    }

    /**
     * Data provider for testName.
     *
     * @return \Iterator
     */
    public static function nameProvider(): \Iterator
    {
        yield [false, false];
        yield [false, true];
        yield [true, false];
        yield [true, true];
    }

    /**
     * Test node name.
     *
     * @param bool $useDefaultNs Use default namespace?
     * @param bool $omitDefault  Omit default namespace from the name?
     *
     * @return void
     */
    #[DataProvider('nameProvider')]
    public function testName(bool $useDefaultNs, bool $omitDefault): void
    {
        $xmlStr = $this->getFixture('xml-with-ns.xml');
        $xml = new XmlDoc();
        $xml->parse($xmlStr);
        $ns = 'http://www.lido-schema.org';
        if ($useDefaultNs) {
            $xml->setDefaultNamespace($ns);
        }
        $nodes = $xml->all($xml->first(path: $useDefaultNs ? 'lido' : "{{$ns}}lido"));
        $expectedPrefix = $useDefaultNs && $omitDefault
            ? ''
            : "{{$ns}}";
        $this->assertCount(2, $nodes);
        $this->assertSame("{$expectedPrefix}lidoRecID", $xml->name($nodes[0], $omitDefault));
        $this->assertSame("{$expectedPrefix}descriptiveMetadata", $xml->name($nodes[1], $omitDefault));
    }

    /**
     * Test invalid input XML.
     *
     * @return void
     */
    public function testInvalidXml(): void
    {
        $xmlStr = $this->getFixture('invalid.xml');
        $xml = new XmlDoc();
        $this->expectExceptionMessage("XML error 'Opening and ending tag mismatch: bar line 3 and baz' at 3:14");
        $xml->parse($xmlStr);
    }

    /**
     * Data provider for testInvalidQuery.
     *
     * @return \Iterator
     */
    public static function invalidQueryProvider(): \Iterator
    {
        yield 'no default ns' => [
            'lido/lidoRecID',
            "'lido' must use correct notation, or default namespace must be defined",
        ];
        yield 'extra {' => [
            '{{lido}lido/lidoRecId',
            'Unexpected repeated { in path: {{lido}lido',
        ];
        yield 'extra }' => [
            '{lido}lido}/lidoRecId',
            'Unexpected } in path: {lido}lido}',
        ];
    }

    /**
     * Test invalid query.
     *
     * @param string $path         Path
     * @param string $exceptionMsg Expected exception message
     *
     * @return void
     */
    #[DataProvider('invalidQueryProvider')]
    public function testInvalidQuery(string $path, string $exceptionMsg): void
    {
        $xmlStr = $this->getFixture('xml-with-ns.xml');
        $xml = new XmlDoc();
        $xml->parse($xmlStr);
        $this->expectExceptionMessage($exceptionMsg);
        $xml->all(path: $path);
    }

    /**
     * Test uninitialized parser.
     *
     * @return void
     */
    public function testUninitializedParser(): void
    {
        $xml = new XmlDoc();
        $this->expectExceptionMessage('No parsed document available');
        $xml->all(path: '{lido}lido');
    }

    /**
     * Test uninitialized parser.
     *
     * @return void
     */
    public function testUninitializedParser2(): void
    {
        $xml = new XmlDoc();
        $this->expectExceptionMessage('No parsed document available');
        $xml->toXML();
    }

    /**
     * Test invalid XML.
     *
     * @return void
     */
    public function testMultipleNamespaces(): void
    {
        $xmlStr = $this->getFixture('xml-with-multiple-ns.xml');
        $xml = new XmlDoc();
        $xml->parse($xmlStr);

        $this->assertSame(
            'FOO',
            $xml->firstValue(path: '{http://www.lido-schema.org}lido/{http://localhost}lidoRecID')
        );
        $this->assertSame(
            'record-id',
            $xml->firstValue(path: '{http://www.lido-schema.org}lido/{http://www.lido-schema.org}lidoRecID')
        );
    }

    /**
     * Test filter.
     *
     * @return void
     */
    public function testFilter(): void
    {
        $xml = new XmlDoc();
        $xml->parse($this->getFixture('xml-with-ns.xml'));
        $ns = 'http://www.lido-schema.org';
        $filterPath = "{{$ns}}lido/{{$ns}}descriptiveMetadata/{{$ns}}objectIdentificationWrap/{{$ns}}titleWrap"
            . "/{{$ns}}titleSet";
        $xml->filter(fn ($node, $path) => $path === $filterPath);
        $this->assertXmlStringEqualsXmlString(
            $this->getFixture('xml-with-ns-filtered.xml'),
            $xml->toXML()
        );

        $xml->parse($this->getFixture('xml-with-ns.xml'));
        $xml->filter(fn ($node, $path) => $xml->name($node) === "{{$ns}}titleSet");
        $this->assertXmlStringEqualsXmlString(
            $this->getFixture('xml-with-ns-filtered.xml'),
            $xml->toXML()
        );
    }

    /**
     * Test notation parsing.
     *
     * @return void
     */
    public function testNotationParsing(): void
    {
        $this->assertSame(
            ['foo', 'name'],
            Notation::parse('{foo}name')
        );
        $this->assertSame(
            ['foo', 'name'],
            Notation::parse('foo name')
        );
        $this->expectExceptionMessage("'foo' is invalid");
        Notation::parse('foo');
    }

    /**
     * Test invalid notation.
     *
     * @return void
     */
    public function testInvalidNotation(): void
    {
        $this->expectExceptionMessage("'foo' is invalid");
        Notation::parse('foo');
    }

    /**
     * Test another invalid notation.
     *
     * @return void
     */
    public function testInvalidNotation2(): void
    {
        $this->expectExceptionMessage("' foo ' is invalid");
        Notation::parse(' foo ');
    }

    /**
     * Get a fixture.
     *
     * @param string $filename Filename
     *
     * @return string
     */
    protected function getFixture(string $filename): string
    {
        return file_get_contents(__DIR__ . '../../../fixtures/' . $filename);
    }
}
