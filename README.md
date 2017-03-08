# AirTemplate

A template engine for PHP devs. Fast, flexible and easy to use and extend.

[![Build Status](https://img.shields.io/travis/ablaser/airtemplate/master.svg?style=flat-square)](https://travis-ci.org/ablaser/airtemplate)
[![Coverage Status](https://img.shields.io/coveralls/ablaser/airtemplate/master.svg?style=flat-square)](https://coveralls.io/github/ablaser/airtemplate?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/ablaser/airtemplate.svg?style=flat-square)](https://scrutinizer-ci.com/g/ablaser/airtemplate/?branch=master)
[![Code Climate](https://img.shields.io/codeclimate/github/ablaser/airtemplate.svg?style=flat-square)](https://codeclimate.com/github/ablaser/airtemplate)
[![TestCoverage](https://img.shields.io/codeclimate/coverage/github/ablaser/airtemplate.svg?style=flat-square)](https://codeclimate.com/github/ablaser/airtemplate/coverage)
[![Latest Version](https://img.shields.io/github/release/ablaser/airtemplate.svg?style=flat-square)](https://packagist.org/packages/airtemplate/airtemplate)

AirTemplate has only one tag: `{{field|option|...}}`. Syntax is inspired by Twig, but it's not exactly the same. So it's more a templating system than a templating language.

However, AirTemplate has -- beside the usual `render` method -- an `each` method which can iterate over arrays and traversable objects, and field options are another powerful way to apply logic to templates and fields.

It's even possible to "include" sub-templates using `render` and `each` options. This allows AirTemplate to render not only flat data structures like those returned from database queries, but also nested arrays, XML and JSON structures.

AirTemplate has grown from a library to a little framework, but it's still much smaller than Twig or Mustache. Most components have no dependencies beside PHP 5.5 or better.

The only exceptions are the `FilesystemLoader` and the `CacheLoader`, which support logging to a PSR-3 compatible logger (for debugging purposes). The `CacheLoader` supports PSR-6 compatible caching too.

## Features

* Lightweight: less than 50k (all components, including comments).
* Clean templates with a simple syntax: `{{field|option|option|...}}`.
* Supports custom field delimiters.
* Efficient: Templates are *parsed only once*.
* Parsed templates can be cached to speed up the initialisation phase.
* Suitable for any size and type of text based output, not only HTML.
* AirTemplate can render flat and nested datastructures (database query results, XML etc).
* *Two* render methods: `render` and `each`.
* Powerful [field processing options](#field-options).
* Supports a memory-saving [*generator mode*](#generator-mode).
* Framework and environment agnostic. Depends only on PHP 5.5+, psr/log and psr/cache.
* Unit tested.
* Conforms to the following [PSR Standards](http://www.php-fig.org/):
	* [PSR-1 Basic Coding Standard](http://www.php-fig.org/psr/psr-1/)
	* [PSR-2 Coding Style Guide](http://www.php-fig.org/psr/psr-2/)
	* [PSR-4 Autoloading Standard](http://www.php-fig.org/psr/psr-4/)
* Supports PSR compatible logging and caching:
	* [PSR-3 Logger Interface](http://www.php-fig.org/psr/psr-3/)
	* [PSR-6 Caching Interface](http://www.php-fig.org/psr/psr-6/)
* Easy installation with composer (and without too).

## Installation

The best way to install AirTemplate is [through composer](http://getcomposer.org).

Just create a composer.json file for your project:

```JSON
{
    "require": {
        "airtemplate/airtemplate": "~0.2"
    }
}
```

Then you can run these two commands to install it:
```
$ curl -s http://getcomposer.org/installer | php
$ php composer.phar install
```

or simply run `composer install` if you have have already [installed the composer globally](http://getcomposer.org/doc/00-intro.md#globally).

Then you can include the autoloader, and you will have access to the library classes:

```php
<?php
require 'vendor/autoload.php';
```
Without composer, you can use AirTemplate's own autoloader, where `path/to/src` is the path to
your installation directory.

```php
<?php
require 'path/to/src/lib/autoload.php';
```

Without autoloading at all, you need to require (or include) the classes as usual.

```php
<?php
require 'path/to/src/ParserInterface.php';
require 'path/to/src/Parser.php';
require 'path/to/src/Loader/LoaderInterface.php';
require 'path/to/src/Loader/Loader.php';
// replace 'ArrayLoader' by 'FilesystemLoader' or 'CacheLoader' as appropriate
require 'path/to/src/Loader/ArrayLoader.php';
require 'path/to/src/EngineInterface.php';
require 'path/to/src/BaseEngine.php';
require 'path/to/src/Engine.php';
require 'path/to/src/Builder.php';
```

## Usage

AirTemplate is split into several components, but for the setup are just two components needed, a *loader* and the *builder*. Loaders are responsible for loading and parsing templates, while the builder creates a rendering engine from these templates.

Three loaders are available, which loads templates from PHP (`ArrayLoader`), from the file system (`FilesystemLoader`) or from cache (`CacheLoader`).

The following example was taken from [https://github.com/bobthecow/mustache.php](https://github.com/bobthecow/mustache.php). The full code is in the examples directory.

This is the view context object representing our data (Chris.php):

```php
<?php
class Chris {
    public $name  = "Chris";
    public $value = 10000;

    public function taxed_value() {
        return $this->value - ($this->value * 0.4);
    }

    public $in_ca = true;
}
```

This is one variant to render this template. Note, that the template has been split into two partials, as AirTemplate has no if/else construct. The condition is simply evaluated in PHP.

```php
require './lib/bootstrap.php';
require './lib/Chris.php';

use AirTemplate\Builder;
use AirTemplate\Loader\ArrayLoader;

$templates = [
    'canonical' => 'Hello {{name}}
You have just won {{value}} dollars!
',
    'in_ca' => 'Well, {{taxed_value|data:taxed_value}} dollars, after taxes.
'
];

$chris = new Chris;

$builder = new Builder(new ArrayLoader);
$engine = $builder->build($templates);

echo $engine->render('canonical', $chris);
if ($chris->in_ca == true) {
    echo $engine->render('in_ca', $chris);
}
```

Well, AirTemplate is flexible and there is another way to create the exact same output:

```php
require './lib/bootstrap.php';
require './lib/Chris.php';

use AirTemplate\Builder;
use AirTemplate\Loader\ArrayLoader;

$templates = [
    'canonical' => 'Hello {{name}}
You have just won {{value}} dollars!
{{in_ca|user:inCa}}',
    'in_ca' => 'Well, {{taxed_value|data:taxed_value}} dollars, after taxes.
'
];

function inCa($value, $field, $data)
{
    global $engine;
    if ($value == false) {
        return '';
    }
    return $engine->render('in_ca', $data);
}

$chris = new Chris;

$builder = new Builder(new ArrayLoader);
$engine = $builder->build($templates);

echo $engine->render('canonical', $chris);
```

Here, we add a field `in_ca` at the end of template `canonical` and apply the user function `inCa` to it. This function renders the `in_ca` template, if chris lives in California or returns an empty string if not. The parameter `$value` is set to `$chris->in_ca` when the function is called.

Now, there is only one render call in the main program flow, and the user function `inCa` decides if it should render the `in_ca` template or not, so the field `in_ca` acts like a conditional field.

Templates may also be stored in files. Assume, the two templates from the example above are stored as two separate files in directory `./templates/mustache-canonical`.

Then, we can use the `FilesystemLoader` to load it:

```php
$templates = [
	'canonical.tmpl',
    'in_ca.tmpl'
];

$builder = new Builder(new FilesystemLoader('./templates/mustache-canonical'));
$engine = $builder->build($templates);
```

The templates array now contains filenames instead of the templates itself. Filenames may also be fully qualified pathnames.

A file mask like `*.tmpl` can also be used, to load a set of template files from the directory given to the constructor of the loader (or set using `setDir`).

```php
$builder = new Builder(new FilesystemLoader('./templates/mustache-canonical'));
$engine = $builder->build('*.tmpl');
```

The `CacheLoader` works exactly the same as the `FilesystemLoader`, but it is able to store parsed templates in a PSR-6 compatible caching system like `symfony/cache`. This may speed up initialisation, because the templates are stored in parsed format and as a single file.

### Render methods

AirTemplate has two render methods: `render` and `each`.

The `render` method (see example above) is used to create a single instance from a template, while `each` can create multiple instances from a template.

```php
echo $engine->render("template-name", $data);
echo $engine->each("template-name", $data[, "separator"[, $rowGenerator]]);
```

The `$data` parameter can be an array or an object, that contains keys or members with the fieldnames used in the template.

If `each` is called with an object, it must be traversable. That means, it must implement a traversable interface (e.g. Traversable, Iterator, Generator). The method has two optional parameters. A separator to be inserted between rendered rows and a [*row generator function*](#generator-mode).

The following is a simple example of the `each` method.

Two templates are defined to build an unordererd list. The `list` template contains one field, `{{items}}`, which will be replaced by the outcome of the `each` method. `each` is called with the `list-item` template and a simple data array. A newline character will be inserted between list items.

Note: The fieldname for simple arrays like in this example will always be `item`.

```php
$templates = [
	'list' => '<ul>
{{items}}
</ul>'
	'list-item' => '<li>{{item|esc}}</li>'
];

$builder = new Builder(new ArrayLoader);
$engine = $builder->build($templates);

echo $engine->render(
	'list',
	[
		'items' => $engine->each(
			'list-item',
			['one', 'two', 'three'],
			"\n"
		)
	]
);
```

And because AirTemplate is flexible, there is another way to do the same.

The `each` method can also be used as an option and applied to the field `items`. The content of the field `items` in the call to `render` is now just the raw data array. The content of this field is then passed to `each`, together with the template name and the separator.

```php
$templates = [
	'list' => '<ul>
{{items|each("list-item", "\n")}}
</ul>'
	'list-item' => '<li>{{item|esc}}</li>'
];

$builder = new Builder(new ArrayLoader);
$engine = $builder->build($templates);

echo $engine->render(
	'list',
	[
		'items' => ['one', 'two', 'three']
	]
);
```

#### Generator Mode

Normally, `each` accumulates the rendered rows in memory and returns it as a string when all rows are rendered. This can lead to growing memory use if there are many rows or if rows have a lot of columns.

The row generator function (see [Generators](http://php.net/manual/en/language.generators.php)) is a way to circumvent this. If such a function is given, AirTemplate works in generator mode and will send rendered rows, one by one, to the function instead of keeping them all in memory. The row generator function can then write the rows to a stream for example. It's an efficient way to render large amounts of data without having 'peaks' in memory usage.

```php
$templates = [
	'list-start' => '<ul>
'
	'list-end' => '</ul>
'
	'list-item' => '<li>{{item|esc}}</li>'
];

// receive list-items one-by-one and write it to the output
// this tiny closure acts as a co-routine to the each-method
$rowGenerator = function() {
	while (true) {
		echo yield;
	}
}

$builder = new Builder(new ArrayLoader);
$engine = $builder->build($templates);

echo $engine->render('list-start');
// echo items in the genarator function
$engine->each('list-item', ['one', 'two', 'three'], PHP_EOL, $rowGenerator());
echo $engine->render('list-end');
);
```

The list template has been split into a `list-start` and `list-end` template, so they can be separately written to the output. The `each` method returns nothing, when using the generator mode. The list items are echoed out in the generator function.

There are two more examples of the generator mode in the `benchmark` directory.

### Templates

Templates in AirTemplate can also be called partials. So in most cases, more than one template is required to render a page, a widget or something else.

Templates consist solely of the template code itself and embedded fields.

#### Fields

Fields are the only element needed by AirTemplate. The syntax is as follows (optional parts in square brackets []):

```
{{fieldname[=[/]datapath][|option][|option]}}
```

The fieldname 'links' this placeholder with an element in the data object or array. A single datapath preceded by an equal sign (=) and one or more options, preceded by a pipe symbol (|) may follow the fieldname.

Templates may contain multiple fields, and the field delimiters can be customized.

##### Custom field delimiters

Custom field delimiters can be set through the loader class constructor. There are two options that must be set: `splitPattern` and `fieldPrefix`. The split-pattern is a regular expression needed for the PHP-function `preg_split`, the prefix is a string.

> One important thing to note is that the regular expression for the *starting delimiter must be enclosed* in parentheses and must also match the field prefix, but the *ending delimiter must NOT be enclosed* in parentheses. This is, because the template parser needs the prefix to recognize the following token as a field name.

To use field delimiters `[@field]`, the options array would look like this:

```php
$options = [
	'splitPattern' => '/(\[@)|\]/',
	'fieldPrefix'  => '[@'
];
$loader = new ArrayLoader($options);
// or
$loader = new FilesystemLoader('path/to/templates', $options);
```

Note, how the parentheses are used within the `split_pattern` to catch the starting field delimiter.

##### Datapath

A datapath is similar to an XPath expression, as is can be used to access nested values in the data object or array. There are some limitations, but it works well with nested arrays and object types like `SimpleXMLElement`. In case of a simple XML object, it is also possible to access attributes.

However, members in a `stdClass` with numeric keys are not accessible using datapath.

Datapath has the following syntax:

```
[/]key[/key]][/@attr]
```

Keys must be separated by a slash. A datapath may be absolute (with a leading slash) or relative to the current field. A leading slash gives access to all keys or properties in the data array or object.

The last element in a datapath may be an attribute, if the data object is a simple XML object.

An example:

```php
$data = [
	'key1' => [
		'key1.1' => [ 'hello' ],
		'key1.2' => [ 'world' ]
	],
	'key2' => [
		'key2.1' => 'abc'
	]
]

$template = [
	'relative' => '{{key1=key1.2}}',
	'absolute' => '{{key1=/key2.1}}'
]
```

Template `relative` will output 'world', while `absolute` will output 'abc' when `render` is called with this data structure.

##### Field Options

Field options are the key to extend the built-in functionality of AirTemplate. They can be used to format or transform field values in any way you like, but also to include sub-templates or turn a field into a logical element.

There are five different variants, which are called with different parameters:

```
// 1
|shortcut
|function
|\Classname::staticMethod

// 2
|function(arg[, arg])

// 3
|user:function
|app:method
|app::staticMethod

// 4
|data:method
|data::staticMethod

// 5
|render("template"[, ?])
|each("template"[, "separator"])
|default("Default Value")
```

1. Shortcuts, Global functions, Static methods<br>
   These are called with a single parameter: the field value.
2. Functions with parameters<br>
   Such functions are called with the specified parameter list. Parameters must be constants (strings, numbers) or a question mark (?) as a placeholder for the field value (e.g. `sprintf("%1.6f", ?)`).
3. User functions and application methods<br>
   These functions are called with three parameters, field value, field name and the data object or array. Function and method names must be prepended by `user:`, `app:` or `app::`.
4. Data object methods<br>
   These will be called without any parameter and are only available, when data (passed to `render` or `each`) is an object. Method names must be prepended with `data:` or `data::`.
5. Built-in methods<br>
   `render` and `each` are used to render sub-templates in nested data-structures. They are called with the template name and the value of the current field.<br>
   The separator in `each` is optional and defaults to an empty string ('').<br>
   Default value must be a (properly encoded) string.

###### Shortcuts, Global functions, Static methods

The simplest form of options works with functions (PHP and custom) that await its input on the first parameter, has no other required parameters and returns a result value. Therefore, many well known functions like `strip_tags`, `md5` and similar functions can be used out of the box.

A few useful shortcuts are defined to make it a bit simpler: `esc`, `escape`, `lcase`, `ucase`, `int`, `float`, `urlenc` and `rawurlenc`.

```
// Capitalize words in field title, then escape it with 'htmlspecialchars'
{{title|ucwords|esc}}
// Rawurlencode the field article_url
{{article_url|rawurlenc}}
// Transform bodytext markdown into HTML
{{bodytext|\Michelf\Markdown::defaultTransform}}
```

###### Functions with parameters

Some useful functions (like `sprintf`) require more than one parameter. Such functions can be called with a parameter list, which is specified as a list of (constant) values between parentheses. The question mark is used as a placehoder for the field value.

The parameter list is parsed with `str_getcsv` and must therefore follow the format defined in the PHP manual. However, the question mark can be written without quotes.

```
// Format the price field with 'sprintf'
// The price field is injected as second parameter, replacing the question mark (?)
{{price|sprintf("$%1.2f", ?)}}
// Convert value to a float and format it using the PHP function 'number_format'
{{value|float|number_format(?, 2, ".", " ")}}
```

###### User functions and application methods

These functions are called with three parameters, field value, field name and the data object or array. User functions are very powerful and can do more than normal functions, because the data object or array originally passed to the render function will also be passed to user functions.

This allows them to create content on the fly using other fields from "data" or rendering this or that, depending on some condition. Fields with a user function can therefore be used as a replacement for the non existing if/else constructs in templates.

User functions can exist in the global scope and within an "app" class. In the latter case, an object must be passed to the constructor of the `Builder`.

```
// call global function 'getArticleCode'
{{article_code|user:getArticleCode|esc}}
// call the static method 'listCategories' from the app-class
{{categories|app::listCategories}}
// call instance method
{{colors|app:showColors}}
```

The `showColors` method for example might look like so:

```php
class AppView
{
    public function showColors($value, $field, $data)
    {
        if (empty($value)) {
            return $this->view->render('no-colors', $data);
        }
        return $this->view->render('color-table', $value);
    }
}

$view = new AppView;

// The view object can be passed to the builder via constructor
$builder = new Builder(new ArrayLoader, $view);

// or it can be later set using the setApp method
$builder->setApp($view);
```

User functions may also alter the data parameter, for example create new array keys or properties on the fly. Such fields may then be used later on within the same template. Just get the data parameter as a reference:

```php
function getArticleCode($value, $field, &$data)
{
    // create a new new field
    $data['new_field'] = ...
    // return article-code
    return $data['type'] . '-' . $data['category1'];
}
```

###### Data object methods

It is also possible to call instance and static methods defined in the data object. These are called without any parameters. However, it is also possibe to use the data object as the app object in the constructor of the `Builder` and then call them using `app:` or `app::`.

```php
// field definition: {{somefield|data:doubleSomething}}
class DataObj
{
    public function doubleSomething()
    {
        return $something * 2;
    }
}
```

###### Built-in methods

Three methods are built-in: `default`, `render` and `each`.

Default inserts the string specified when the value parameter is empty. Options specified after `default` are ignored in this case, so the value must be properly encoded.

`render` and `each` are used to include sub-templates. They are called with the template specified and the value of the field they are attached to. The `render` method can also be called with the same data object or array as the current render method was called with, by specifying a question mark (?) as second parameter. This makes it possible to break up flat data structures in groups and render them with separate sub-templates.

The separator in `each` is optional and defaults to an empty string ('').

The example below shows how to use `each` to render a table in one go. The fields `thead` and `tbody` in the data parameter passed to render are both arrays. The same fields in the template have the `each` option specified which
calls the `each` method with the content of the fields they belong to.

```php
$data = [
  ['name' => 'Bob', 'email' => 'bob@example.com'],
  ['name' => 'Mary', 'email' => 'mary@example.com'],
  ['name' => 'Jenny', 'email' => 'jenny@example.com'],
];

$templates = [
	'table' => '<table>
<thead>
<tr>
{{thead|each("th", "\n")}}
</tr>
</thead>
<tbody>
{{tbody|each("tr", "\n")}}
</tbody>
</table>',
	'th' => '<th>{{item|esc}}</th>',
	'tr' => '<tr>
<td>{{name|esc}}</td>
<td><a href="mailto:{{email}}">{{email|esc}}</a></td>
</tr>'
];

// render the table
echo $engine->render(
	'table',
	[
		'thead' => ['Username', 'Email'],
		'tbody' => $data
	]
);
```

### Example Code

There are some (commented) examples in the [examples directory](./examples) and in [benchmark](./benchmark). The latter have simple benchmark tests included, giving some hints about rendering times and memory usage.

Please note that the displayed memory consumption values may have strong variations when compared between different platforms and PHP versions (see discussion on stackoverflow: [PHP memory_get_usage](http://stackoverflow.com/questions/4010781/php-memory-get-usage).

## License

The MIT [License](./LICENSE).

The testdata used in the benchmark tests, is an extract from the public sample data `medsamp2016.xml`, available from the [U.S. National Library of Medicine](https://www.nlm.nih.gov/bsd/sample_records_avail.html).
