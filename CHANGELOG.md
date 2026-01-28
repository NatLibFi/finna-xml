### Development

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.3.0] - 2026-01-28

### Added

- New method `filter()` can now be used to filter nodes from the document (similar to array_filter).

## [1.2.0] - 2026-01-26

### Added

- New method `name()` can now be used to get the node name (optionally without any default namespace).

## [1.1.1] - 2026-01-13

### Fixed

- Empty elements in non-wrapped XML string caused the next element to be skipped.

## [1.1.0] - 2026-01-09

### Added

- New method `root()` can now be used to get the root node (e.g. `$rootValue = $xmlDoc->value($xml->root());`).
- `toXML()` now has a parameter for enabling trimming of leading/trailing whitespace of text nodes when rendering XML.

### Fixed

- Namespace declarations were duplicated when outputting XML if there were attributes with those namespaces in the root node.
- Selection with empty path, while useless, did not return all sub-nodes as expected.

## [1.0.1] - 2026-01-08

### Fixed

- Some records were not properly parsed.

## [1.0.0] - 2026-01-08

Initial release.
