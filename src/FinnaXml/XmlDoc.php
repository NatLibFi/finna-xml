<?php

/**
 * XML Document
 *
 * PHP version 8
 *
 * Copyright (C) 2025-2026 University of Helsinki, National library of Finland.
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

use InvalidArgumentException;
use RuntimeException;

use function is_array;

/**
 * XML Document
 *
 * @category FinnaXml
 * @package  FinnaXml
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  https://opensource.org/license/mit The MIT License
 * @link     https://github.com/NatLibFi/finna-xml Git Repo
 */
class XmlDoc
{
    /**
     * Parsed XML.
     *
     * @var ?array
     */
    protected ?array $parsed = null;

    /**
     * Default namespace URI for path parts, elements and attributes without namespace.
     *
     * @var ?string
     */
    protected ?string $defaultNamespace = null;

    /**
     * Default namespace prefix for path parts, elements and attributes without namespace.
     *
     * @var ?string
     */
    protected ?string $defaultNamespacePrefix = null;

    /**
     * Parse an XML string.
     *
     * @param string $xml XML
     *
     * @return static
     */
    public function parse(string $xml): static
    {
        $this->parsed = (new XmlParser())->parse($xml);
        return $this;
    }

    /**
     * Serialize the document as XML.
     *
     * @param int  $indent Indent (pretty-print) by $indent spaces
     * @param bool $trim   Trim leading and trailing whitespace from text nodes?
     *
     * @return string
     */
    public function toXML(int $indent = 0, bool $trim = false): string
    {
        if (null === $this->parsed) {
            throw new RuntimeException('No parsed document available');
        }

        return (new XmlRenderer($this->parsed, $this->defaultNamespace, $this->defaultNamespacePrefix))
            ->render($indent, $trim);
    }

    /**
     * Import a previously parsed document array.
     *
     * @param array $parsed Parsed record.
     *
     * @return void
     */
    public function import(array $parsed): void
    {
        if (!(new XmlParser())->validate($parsed)) {
            throw new RuntimeException('Invalid parsed document format');
        }
        $this->parsed = $parsed;
    }

    /**
     * Export a previously parsed document array.
     *
     * @return array
     */
    public function export(): array
    {
        if (null === $this->parsed) {
            throw new RuntimeException('No parsed document available');
        }
        return $this->parsed;
    }

    /**
     * Set default namespace for path queries.
     *
     * @param ?string $namespace Namespace URI, or null for no default
     * @param ?string $prefix    Prefix for any default namespace (used when rendering XML)
     *
     * @return static
     */
    public function setDefaultNamespace(?string $namespace, ?string $prefix = null): static
    {
        $this->defaultNamespace = $namespace;
        $this->defaultNamespacePrefix = $prefix;
        return $this;
    }

    /**
     * Get root node.
     *
     * @return ?array Root node, or null if uninitialized
     */
    public function root(): ?array
    {
        return $this->parsed['data'] ?? null;
    }

    /**
     * Get all nodes by path starting from the given single node.
     *
     * @param ?array       $node Node to start from (optional)
     * @param string|array $path Path (array or a slash-delimited string) with each node either in Clark notation, or
     * just node name with $this->defaultNamespace defined
     *
     * @return array[]
     */
    public function all(?array $node = null, string|array $path = ''): array
    {
        return $this->allByPath($node, $path);
    }

    /**
     * Get first node by path.
     *
     * @param ?array       $node Node to start from (optional)
     * @param string|array $path Path (array or a slash-delimited string) with each node either in Clark notation, or
     * just node name with $this->defaultNamespace defined
     *
     * @return ?array
     */
    public function first(?array $node = null, string|array $path = ''): ?array
    {
        return $this->all($node, $path)[0] ?? null;
    }

    /**
     * Get all node values by path starting from the given single node.
     *
     * @param ?array       $node        Node to start from (optional)
     * @param string|array $path        Path (array or a slash-delimited string) with each node either in Clark
     * notation, or just node name with $this->defaultNamespace defined
     * @param bool         $trim        Trim results?
     * @param bool         $emptyValues Include empty values?
     *
     * @return string[]
     */
    public function allValues(
        ?array $node = null,
        string|array $path = '',
        bool $trim = true,
        bool $emptyValues = false
    ): array {
        $results = $this->getValues($this->all($node, $path));
        if (!$emptyValues) {
            $results = array_values(array_filter($results, fn ($s) => '' !== $s));
        }
        return $trim ? array_map('trim', $results) : $results;
    }

    /**
     * Get first node value as string by path.
     *
     * @param ?array       $node Node to start from (optional)
     * @param string|array $path Path (array or a slash-delimited string) with each node either in Clark notation, or
     * just node name with $this->defaultNamespace defined
     * @param bool         $trim Trim result?
     *
     * @return ?string
     */
    public function firstValue(?array $node = null, string|array $path = '', bool $trim = true): ?string
    {
        $first = $this->first($node, $path);
        $result = $first['val'] ?? null;
        return ($trim && null !== $result) ? trim($result) : $result;
    }

    /**
     * Get attribute from a node
     *
     * @param ?array $node Node
     * @param string $attr Attribute either in Clark notation, or just name with $this->defaultNamespace defined
     * @param bool   $trim Trim result?
     *
     * @return ?string
     */
    public function attr(?array $node, string $attr, bool $trim = true): ?string
    {
        // Try to find the attribute first with namespace and fall back to search without namespace:
        $result = $node['attrs'][Notation::ensureValid($attr, $this->defaultNamespace)]
            ?? $node['attrs'][$attr]
            ?? null;
        return ($trim && null !== $result) ? trim($result) : $result;
    }

    /**
     * Get the string value of a node
     *
     * @param array $node Node
     * @param bool  $trim Trim result?
     *
     * @return string
     */
    public function value(array $node, bool $trim = true): string
    {
        return $trim ? trim($node['val']) : $node['val'];
    }

    /**
     * Recursively traverse all branches by path and return any values found.
     *
     * @param ?array       $root Node to start from
     * @param string|array $path Path (array or a slash-delimited string) with each node either in Clark notation
     * just node name with $this->defaultNamespace defined
     *
     * @return array
     */
    protected function allByPath(?array $root, string|array $path): array
    {
        $currentNodes = $root['sub'] ?? $this->root()['sub'] ?? null;
        if (null === $currentNodes) {
            throw new RuntimeException('No parsed document available');
        }
        if (!$path) {
            return $currentNodes;
        }
        $remainingPath = is_array($path) ? $path : $this->explodePath($path);
        $pathPart = array_shift($remainingPath);

        // Verify that the path part has namespace:
        $pathPart = Notation::ensureValid($pathPart, $this->defaultNamespace);

        // Try to find nodes first with namespace and fall back to search without namespace:
        foreach ([false, true] as $fallback) {
            if ($fallback) {
                $clark = Notation::parse($pathPart);
                $pathPart = '{}' . $clark[1];
            }
            $result = null;
            foreach ($currentNodes as $node) {
                if ($pathPart === $node['name']) {
                    if ($remainingPath) {
                        if ($node['sub']) {
                            $result = [
                                ...($result ?? []),
                                ...$this->allByPath($node, $remainingPath),
                            ];
                        }
                    } else {
                        $result[] = $node;
                    }
                }
            }
            if (null !== $result) {
                return $result;
            }
        }

        return [];
    }

    /**
     * Get values from an array of nodes
     *
     * @param array $nodes Nodes
     *
     * @return string[]
     */
    protected function getValues(array $nodes): array
    {
        return array_map(
            function ($node): string {
                return $node['val'];
            },
            $nodes
        );
    }

    /**
     * Explode a path string to an array
     *
     * @param string $path Path
     *
     * @return array
     */
    protected function explodePath(string $path): array
    {
        if (!str_contains($path, '/')) {
            return [$path];
        }
        if (!str_contains($path, '{')) {
            return explode('/', $path);
        }
        $parts = [];
        $collected = '';
        $inNs = false;
        foreach (str_split($path) as $c) {
            switch ($c) {
                case '{':
                    if ($inNs) {
                        throw new InvalidArgumentException('Unexpected repeated { in path: ' . $path);
                    }
                    $inNs = true;
                    break;
                case '}':
                    if (!$inNs) {
                        throw new InvalidArgumentException('Unexpected } in path: ' . $path);
                    }
                    $inNs = false;
                    break;
                case '/':
                    if (!$inNs) {
                        $parts[] = $collected;
                        $collected = '';
                        continue 2;
                    }
                    break;
            }
            $collected .= $c;
        }
        if ('' !== $collected) {
            $parts[] = $collected;
        }
        return $parts;
    }
}
