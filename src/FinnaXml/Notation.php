<?php

/**
 * Notation Support Functions.
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

/**
 * Notation Support Functions.
 *
 * @category FinnaXml
 * @package  FinnaXml
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  https://opensource.org/license/mit The MIT License
 * @link     https://github.com/NatLibFi/finna-xml Git Repo
 */
class Notation
{
    /**
     * Get namespace and local name from a string.
     *
     * @param string $name Element or attribute name
     *
     * @return array ['namespace', 'local name']
     *
     * @throws \InvalidArgumentException
     */
    public static function parse(string $name): array
    {
        $result = static::tryParse($name);
        if (null === $result) {
            throw new \InvalidArgumentException("'$name' is invalid");
        }
        return $result;
    }

    /**
     * Get namespace and local name from a string.
     *
     * @param string $name Element or attribute name
     *
     * @return array ['namespace', 'local name'], or null on failure
     */
    public static function tryParse(string $name): ?array
    {
        $parts = explode(' ', $name);
        // There should be no extra spaces regardless of the format:
        if (isset($parts[2])) {
            return null;
        }
        if (!isset($parts[1])) {
            // No space found; check for Clark format:
            if (str_starts_with($name, '{') && false !== ($p = strpos($name, '}'))) {
                return [substr($name, 1, $p - 1), substr($name, $p + 1)];
            }
            return null;
        }
        return $parts;
    }

    /**
     * Ensure a node or attribute name is presented in a valid notation style.
     *
     * @param string  $name      Name
     * @param ?string $defaultNs Default namespace to apply for non-prefixed name
     *
     * @return string
     */
    public static function ensureValid(string $name, ?string $defaultNs): string
    {
        if ($parsed = static::tryParse($name)) {
            return '{' . $parsed[0] . '}' . $parsed[1];
        }
        if (null === $defaultNs) {
            throw new \InvalidArgumentException(
                "'$name' must use correct notation, or default namespace must be defined"
            );
        }
        return '{' . $defaultNs . '}' . $name;
    }
}
