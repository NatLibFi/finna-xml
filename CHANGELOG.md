### Development

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.6.0] - 2026-02-03

### Added

- New `attrs()` method can be used to retrieve all attributes of a node.

# Changed

- The `localName()` method now allows also a qualified name to be passed to retrieve a local name for it.


## [1.5.0] - 2026-02-02

### Added

- An option was added to leave out namespace prefix from rendered XML when there's only a single one.
- New `localName()` method can be used to retrieve the local name (non-namespaced) of a node.
- New `removeChildren()` method can be used to remove all child nodes of a node during `modify()`.
- New `replaceChildren()` method can be used to replace all child nodes of a node with another XmlDoc during `modify()`.
- The callback used with `filter()` and `modify()` is now passed a stack of parent nodes for reference.

# Changed

- If a prefix for a namespace is not defined, one is generated automatically when rendering the XML.

# Fixed

- Several aspects of attribute handling particularly when a default namespace is not defined have been fixed.


## [1.4.0] - 2026-01-29

### Added

- New method `modify()` can now be used to modify nodes of the document (similar to `filter()`). Support methods `setName()`, `setValue()`, `setAttr()` and `addChild()` are available for node manipulation.
- Starting node can now be specified when exporting or serializing a record.

## [1.3.1] - 2026-01-28

### Changed

- Tooling updated.

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
