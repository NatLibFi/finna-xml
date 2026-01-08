
Finna-XML
=========

Introduction
------------
Finna-XML is yet another XML handling library for PHP. It's based on PHP's XMLReader and XMLWriter. The resulting
array format is inspired by [sabre-xml](https://sabre.io/xml/).

The main goals for the library are:

- To make it easy to extract information from repeated XML elements.
- To make it easy to manage multiple namespaces and records that are missing namespace definitions.
- To do things quickly for a large number of XML records. Data structure is just a collection of associative arrays avoiding overhead of working with objects (though it makes some things more cumbersome).
- To make it possible to serialize and unserialize the parsed array without loss of information.

Features
--------
- Parse XML regardless of namespaces.
- Find contents in XML easily even when using multiple namespace.
- Render the XML as string (simple cases also with namespace added where missing).
- Serialize/unserialize the parsed structure for caching or other re-use.

Installation
------------
The recommended method for incorporating this library into your project is to use
Composer (http://getcomposer.org):

`composer require natlibfi/finna-xml`

Usage
-----

All queries for the document contents are done with paths. Even immediate children of a node can be retrieved with a path. While the path queries resemble XPath, Finna-XML does not support any actual XPath features. All queries are relative to the starting node (or root node, if omitted), and there is currently no support for attribute filtering etc. This allows the queries to be as quick as possible.

If you need proper XPath support, use something else. :)

The following notations are supported for searching element and attribute names:

Clark notation: `{namespace-uri}localName`

This notation is used internally and required when there are namespaces in multi-level path queries.

Note that if you have a variable for a namespace URI (which you usually should), you'll need to take PHP's use of curly brackets into account in string interpolation. So if you use double quotes, also double the brackets: `$xmlDoc->all($node, "{{$namespaceUri}}element")`.

Space notation: `namespace-url localName`

This notation can be used for a single-level path query.

Examples
--------

See the examples directory for actual examples.
