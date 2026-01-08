<?php

/**
 * XML Renderer
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
 * @package  FinnaXml
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  https://opensource.org/license/mit The MIT License
 * @link     https://github.com/NatLibFi/finna-xml Git Repo
 */

declare(strict_types=1);

namespace FinnaXml;

use RuntimeException;
use XMLWriter;

/**
 * XML Renderer
 *
 * This is a simple XML renderer.
 *
 * @category FinnaXml
 * @package  FinnaXml
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  https://opensource.org/license/mit The MIT License
 * @link     https://github.com/NatLibFi/finna-xml Git Repo
 */
class XmlRenderer
{
    /**
     * Constructor
     *
     * @param array   $parsed                 Array of parsed data
     * @param ?string $defaultNamespace       Default namespace for elements missing a namespace, or null for none
     * @param ?string $defaultNamespacePrefix Default namespace prefix for default namespace, or null for none
     */
    public function __construct(
        protected array $parsed,
        protected ?string $defaultNamespace,
        protected ?string $defaultNamespacePrefix
    ) {
    }

    /**
     * Render the parsed array as an XML string.
     *
     * @param int $indent Indent (pretty-print) by $indent spaces
     *
     * @return string
     */
    public function render(int $indent = 0): string
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->setIndent((bool)$indent);
        $writer->setIndentString(str_repeat(' ', $indent));
        $writer->startDocument();
        $this->nodeToXML($writer);
        $writer->endDocument();
        return $writer->flush();
    }

    /**
     * Write a node to XMLWriter.
     *
     * @param XMLWriter $writer XMLWriter
     * @param ?array    $node   Node to write, or null to start from root
     *
     * @return void
     */
    protected function nodeToXML(XMLWriter $writer, ?array $node = null): void
    {
        $current = $node ?? $this->parsed['data'];
        [$elementNs, $localName] = Notation::parse($current['name']);
        $elementNs = $elementNs ?: $this->defaultNamespace;
        $elementNsPrefix = null;
        if ($elementNs) {
            if (null === ($elementNsPrefix = $this->getNamespacePrefix($elementNs))) {
                throw new RuntimeException("No prefix found for namespace $elementNs");
            }
            // Output namespace declaration only for root element (and not for xml namespace):
            if (null === $node && 'xml' !== $elementNsPrefix) {
                $writer->startElementNs($elementNsPrefix, $localName, $elementNs);
            } else {
                $writer->startElement("$elementNsPrefix:$localName");
            }
        } else {
            $writer->startElement($localName);
        }
        // Write namespaces for root node:
        if (null === $node && $this->parsed['namespaces']) {
            $addNamespaces = $this->parsed['namespaces'];
            if ($this->defaultNamespace && $this->defaultNamespacePrefix) {
                $addNamespaces[$this->defaultNamespacePrefix] = $this->defaultNamespace;
            }
            unset($addNamespaces['xml']);
            unset($addNamespaces['xsi']);
            if ($elementNsPrefix) {
                unset($addNamespaces[$elementNsPrefix]);
            }
            foreach ($addNamespaces as $prefix => $ns) {
                $writer->startAttributeNs('xmlns', $prefix, '');
                $writer->text($ns);
                $writer->endAttribute();
            }
        }
        // Write attributes:
        foreach ($current['attrs'] as $attr => $value) {
            if ($parsed = Notation::tryParse($attr)) {
                [$ns, $localName] = $parsed;
                $ns ??= $this->defaultNamespace;
            } else {
                $ns = $attr === 'schemaLocation'
                    ? 'http://www.w3.org/2001/XMLSchema-instance'
                    : $this->defaultNamespace;
                $localName = $attr;
            }
            if ($ns) {
                if (null === ($prefix = $this->getNamespacePrefix($ns))) {
                    throw new RuntimeException("No prefix found for namespace $ns");
                }

                // Write namespace only if it differs from the element's namespace, and is not xml:
                if ($ns !== $elementNs && 'xml' !== $prefix) {
                    $writer->startAttributeNs($prefix, $localName, $ns);
                } else {
                    $writer->startAttribute("$prefix:$localName");
                }
            } else {
                $writer->startAttribute($localName);
            }
            $writer->text($value);
            $writer->endAttribute();
        }
        if ('' !== $current['val']) {
            $writer->text($current['val']);
        }
        // Sub-nodes:
        foreach ($current['sub'] as $subNode) {
            $this->nodeToXML($writer, $subNode);
        }
        $writer->endElement();
    }

    /**
     * Get namespace prefix for a namespace.
     *
     * @param string $ns Namespace
     *
     * @return ?string
     */
    protected function getNamespacePrefix(string $ns): ?string
    {
        if ($ns === $this->defaultNamespace) {
            return $this->defaultNamespacePrefix;
        }
        if (false !== ($prefix = array_search($ns, $this->parsed['namespaces']))) {
            return $prefix;
        }
        return null;
    }
}
