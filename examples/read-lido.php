<?php
require 'vendor/autoload.php';

$ns = 'http://www.lido-schema.org';
$xmlDoc = new \FinnaXml\XmlDoc();
$xmlDoc->parse(file_get_contents(__DIR__ . '/../tests/fixtures/xml-with-ns.xml'));

// Option 1: Explicitly defined namespace for each path element:
$nsClark = '{' . $ns . '}';
$path = "{$nsClark}lido/{$nsClark}descriptiveMetadata/{$nsClark}objectIdentificationWrap/{$nsClark}titleWrap/{$nsClark}titleSet/{$nsClark}appellationValue";
$preferred = [];
$alternative = [];
foreach ($xmlDoc->all(path: $path) as $title) {
    $pref = $xmlDoc->attr($title, "$ns pref");
    if ('preferred' === $pref) {
        $preferred[] = $xmlDoc->value($title);
    } elseif ('alternative' === $pref) {
        $alternative[] = $xmlDoc->value($title);
    }
}
echo "Preferred titles: " . PHP_EOL . implode(PHP_EOL, $preferred) . PHP_EOL . PHP_EOL;
echo "Alternative titles: " . PHP_EOL . implode(PHP_EOL, $preferred) . PHP_EOL . PHP_EOL;

// Option 2: Default namespace:
$xmlDoc->setDefaultNamespace($ns);
$path = "lido/descriptiveMetadata/objectIdentificationWrap/titleWrap/titleSet/appellationValue";
$preferred = [];
$alternative = [];
foreach ($xmlDoc->all(path: $path) as $title) {
    $pref = $xmlDoc->attr($title, "pref");
    if ('preferred' === $pref) {
        $preferred[] = $xmlDoc->value($title);
    } elseif ('alternative' === $pref) {
        $alternative[] = $xmlDoc->value($title);
    }
}
echo "Preferred titles: " . PHP_EOL . implode(PHP_EOL, $preferred) . PHP_EOL . PHP_EOL;
echo "Alternative titles: " . PHP_EOL . implode(PHP_EOL, $preferred) . PHP_EOL . PHP_EOL;

// Option 3: Path as an array:
$path = ["$ns lido", "$ns descriptiveMetadata", "$ns objectIdentificationWrap", "$ns titleWrap", "$ns titleSet", "$ns appellationValue"];
$preferred = [];
$alternative = [];
foreach ($xmlDoc->all(path: $path) as $title) {
    $pref = $xmlDoc->attr($title, "$ns pref");
    if ('preferred' === $pref) {
        $preferred[] = $xmlDoc->value($title);
    } elseif ('alternative' === $pref) {
        $alternative[] = $xmlDoc->value($title);
    }
}
echo "Preferred titles: " . PHP_EOL . implode(PHP_EOL, $preferred) . PHP_EOL . PHP_EOL;
echo "Alternative titles: " . PHP_EOL . implode(PHP_EOL, $preferred) . PHP_EOL . PHP_EOL;

