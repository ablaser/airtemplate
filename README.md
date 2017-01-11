# AirTemplate

A template engine for PHP devs. Lightweight, flexible and easy to use and extend in PHP.

Templates *contain no logic*, just fields (named placeholders), but *logic can be applied* to these fields instead. This results in a complete separation of logic, template and content.

AirTemplate is not a framework, just a library with two classes: The base class `Template` and `FileTemplate` which is an extension of `Template`. `Template` works in memory only, while `FileTemplate` provides access to templates stored as files.

It doesn't need much ressources, because it works internally mainly with arrays instead of instantiating objects for each template.

## Features

* Lightweight: less than 250 lines of PHP-code (without comments).
* Clean templates with a simple syntax: `{{field}}`.
* Supports custom field delimiters.
* Efficient: Templates are *parsed only once*, when loaded.
* Suitable for any size and type of text based output, not only HTML.
* *Two* render methods: `render` and `each`.
* Powerful field processing options using [PHP functions](#process-field-values-using-php-functions) and [*custom field render functions*](#using-custom-field-render-functions).
* Supports a memory-saving [*generator mode*](#using-the-generator-mode).
* Framework and environment agnostic, the base class `Template` depends only on PHP 5.4+.
* Unit tested.
* Compliant with [PSR-1 Coding Standard](http://www.php-fig.org/psr/psr-1/) and [PSR-2 Coding Style Guide](http://www.php-fig.org/psr/psr-2/).
* Supports [PSR-4 Autoloading Standard](http://www.php-fig.org/psr/psr-4/).

## Installation

The best way to install AirTemplate is [through composer](http://getcomposer.org).

Just create a composer.json file for your project:

```JSON
{
    "require": {
        "airtemplate/airtemplate": "~0.1"
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

Without autoloading, you need to require (or include) the classes as usual.

Remember to require the `Template` too when using `FileTemplate`, because it's an extension of the `Template` class.

```php
<?php
require 'path/to/Template.php';
require 'path/to/FileTemplate.php';
```

## Usage

AirTemplate works after this principle:

* Create object instance
* Load templates
* Use the templates to render output

The templates will be parsed once when they are loaded. After that, there is no more parsing or searching needed to render these templates.

### Templates

Templates are pieces of code or markup, interspersed with fields. Templates can have no fields at all or as much fields as required. Fields are delimited by `{{` and `}}`, but other delimiters may be specified through the class constructor.

> Fields can contain any kind of text based content, also rendered partials or widgets. This makes it easy to include such content provided by external sources like CMS functions or web-services.

AirTemplate typically needs a set of named templates to render the output. This is the reason why templates must be organised as an array when they are defined in PHP and used with the `Template` class.

Most examples uses the `Template` class and the following templates:

```php
$templates = array(
	'hello'       => '<p>Hello <b>{{who}}</b>!</p>',
	'rendered-by' => '<p>Content rendered by <b>{{product}}</b> at {{time}}.</p>',
	'pi'          => '<p>This is the value of Pi: <b>{{pi}}</b></p>',
	'list'        => '<ul>
{{items}}
</ul>'
	'list-item'   => '<li>{{item}}</li>'
);
```

Templates may also be stored as files and used with the [`FileTemplate` class](#using-the-extended-filetemplate-class). A templates array is not needed in this case. The file basename (incl. extension) is used as the template name and array key.

There are some sample templates in [examples/templates](./examples/templates), which are used in the menu-page example [`index.php`](./examples/index.php).

### Using the base class `Template`

The following code creates an instance as usual and loads the templates from above using the `setTemplate` method.

```php
$engine = new AirTemplate\Template;
$engine->setTemplates($templates);
```

It's also possible to 'compose' a template set from multiple source template arrays, when the second parameter is set to true. In this case, the new templates will be merged using `array_merge`.

> If duplicate keys (template names) exist, then the template from the merged array will override the existing template.

Building a template set from multiple source arrays. The `setTemplates`, `setParsedTemplates` methods from the `Template` class and also `loadTemplates` and `loadParsedTemplates` from `FileTemplate` are chainable like shown below.

```php
$engine
	->setTemplates($templates)
	->setTemplates($templates2, true);
```

#### Custom field delimiters

Custom field delimiters can be set using an array through the class constructor. There are two options that must be set: `splitPattern` and `fieldPrefix`. The split-pattern is a regular expression needed for the PHP-function `preg_split`, the prefix is a string.

> One important thing to note is that the regular expression for the *starting delimiter must be enclosed* in parentheses and must also match the field prefix, but the *ending delimiter must NOT be enclosed* in parentheses. This is, because the template parser needs the prefix to recognize the following token as a field name.

To use field delimiters `[@field]`, the options array would look like this:

```php
$options = array(
	'splitPattern' => '/(\[@)|\]/',
	'fieldPrefix'  => '[@'
);
$engine = new AirTemplate\Template($options);
```

Look, how the parentheses are used within the `split_pattern` to 'catch' the starting field delimiter.

### Creating output using `render`

The `render` method is used to create an instance from a template. The method signature is as follows:

```php
public function render(
	$name,
	$data = array(),
	$options = array()
)
```

`render` accepts 1-3 parameters:

* *name*: The template name.
* *data*: The data array or object.
* *options*: The field options array.

The data array or object typically contains values for the fields in the template, while the options array may contain instructions to be applied to the fields.

The field-names defined in the template are used to 'lookup' values in the data and options arrays.

#### Field options

The power and flexibility of AirTemplates lays in the field options, as these can be used to *apply* PHP functions or custom field render functions to fields when they are rendered.

Render options can be set on a per-field basis, but a wildcard ('*') can be used to apply an option to all fields in the template. The wildcard option is ignored, if a field-specific function is also defined.

Multiple options can be applied to a single field, by wrapping them in an array.

Without an option, the field value will be returned 'as is'. If there is also no field value, an empty string ('') is returned instead.

Possible use cases for field options are:

* Process field values using PHP functions like `htmlspecialchars`, `sprintf`, `strip_tags` and similar functions.
* Apply custom field render functions which can do even more:
	* Transform and modify the field value (e.g. render Markdown or Textile).
	* Access data in deeper nested levels of the data array or in sub-objects.
	* Create field content on the fly (e.g. calculate an MD5 hash from a string value).
	* Create and add fields to the data array (data objects may or may not support this feature).
	* Request data or content from external sources (e.g. Databases, Web-Services, Sensors).
	* Use CMS or application functions and methods that render widgets or partials.

##### Process field values using PHP functions

There are two methods to apply standard functions to a field.

```php
// simple method
$options = ['fieldname' => 'function name'];
// complex method
$options = ['fieldname' => ['function name', <param>, <param>, ...]];
```

The simpler method with only a function-name can only be used with functions (PHP and custom) that await its input on the first parameter, has no other required parameters and returns a result value.

This is sufficient for many popular PHP functions like these:

```php
$options = ['fieldname' => 'htmlspecialchars'];
$options = ['fieldname' => 'rawurlencode'];
$options = ['fieldname' => 'strip_tags'];
$options = ['fieldname' => 'md5'];
```

The complex method needs an array, but it makes functions available which requires more than one parameter. The first element of that array must be the name of the function, while following elements are the parameters passed to the function in the specified order.

Some examples:

```php
$options = ['fieldname' => ['sprintf', '%1.6f', Template::FIELD_VALUE]];
$options = ['fieldname' => ['htmlspecialchars', Template::FIELD_VALUE, ENT_HTML5]];
```

A special parameter value, `Template::FIELD_VALUE`, is used as a placeholder for
the position where the field value should be passed to the function.

##### Custom field render functions

You can also define your own [*field render functions*](#using-custom-field-render-functions) to process the field value. They an be implemented as class methods (static and non-static) or anonymous functions (closures).

These functions can do more, because they get not only the value but also context information from the template engine.

Four parameters are passed to field render functions:

* *value*: The field value from the data array or object.
* *field*: The name of the field currently being rendered.
* *data*: The data array or object passed to `render` or `each`.
* *isObject*: A flag, that is set to true, if the data parameter is an object.

The data parameter can optionally be passed by reference. This can - for example - be used to reduce the number of function calls, if multiple fields should be created dynamically. In that case, you can define a single function which creates these fields and updates the data array, so they can be used later on in the same template, when these fields must be rendered.

This scenario is generally possible when the data parameter is an array. However, it may or may not be possible when data is an object.

##### Multiple render options

Multiple options can simpliy be specified by wrapping them in an array. The field value will be 'piped' through the defined options in a 'first-in first-out' order. So the original value will be passed as input to the first option, and the result of this will be passed to the second option and so on.

Options can be PHP functions and also custom field render functions.

```php
$options = ['fieldname' => ['option1', 'option2', 'option3']];
```

Suppose we want a to create a string that must be ecaped and also padded to the right with dots. We need two PHP functions to achieve this, `sprintf` and `htmlspecialchars`.

```php
$templates = [
	'test' => '<pre>{{str}}</pre>'
];
$options = [
	'str' => [
		['sprintf', "%'.-5s", Template::FIELD_VALUE],
		'htmlspecialchars'
	]
];
echo $engine->render('test', ['str' => '&'], $options)
```

This will first pad the string with four spaces, then it is escaped. The result is `<pre>&amp;....</pre>`, which will correctly be displayed in the browser as an ampersand followed by four dots.

Multiple options are probably most useful in combination with the `each` method. Array values can be formatted without the need to traverse it and apply the formatting before it is rendered (and traversed again).

### Render arrays using `each`

`each` creates an instance of the supplied template for each item in the data array. It works with simple arrays, but also with two-dimensional arrays like those returned by database queries.

This is the function signature of `each`:

```php
public function each(
	$name,
	$data,
	$options = array(),
	$separator = '',
	$rowGenerator = null
)
```

`each` accepts 2-5 parameters:

* *name*: The template name.
* *data*: The data array.
* *options*: The field options array.
* *separator*: An optional separator to be inserted between rendered rows.
* *rowGenerator*: An optional PHP generator function, that receives rendered rows, one-by-one.

Parameters 1 and 3 are the same as with `render`, but the data parameter is required and must be an array. A separator which is inserted between rendered rows may be specified. In the last parameter, a [PHP generator function](#using-the-generator-mode) can be specified.

The data parameter is an array that is traversed with a `foreach` loop. Defined options will be applied to the fields of each row.

The individual items of 1-dimensional arrays are accessible in templates using the default field-name `{{item}}` (see template "list-item"). For two-dimensional arrays, the array keys of the row are used as field names.

The following code renders a simple, one-dimensional array as an unordered list. `htmlspecialchars` is applied to each array item and a newline character is added between rendered list items.

```php
echo $engine->render(
	'list',
	[
		'items' => $engine->each(
			'list-item',
			['one', 'two', 'three'],
			['item' => 'htmlspecialchars'],
			"\n"
		)
	]
);
```

#### Using custom field render functions

This is an advanced example that renders a two-dimensional array and uses a field render function to create computed (or derived) fields.

We create two new fields, `email` and `email_link`, and add them to the data array. This happens in the closure function that is assigned to `$username`. This function will be executed for each row in the data array, when the field `username` is rendered. The generated values will be used later to fill the fields.

Note that the `$data` array must be passed by reference, so we can modify it within the closure.

The currently processed field and fields that follow can be generated. Fields preceding the current field in the template are not changable anymore.

```php
$data = [
  ['username' => 'bob', 'domain' => 'example.com'],
  ['username' => 'mary', 'domain' => 'example.com'],
  ['username' => 'jenny', 'domain' => 'example.com'],
];

$templates = [

	'table' => '<table>
<thead>
<tr>
{{thead}}
</tr>
</thead>
<tbody>
{{tbody}}
</tbody>
</table>',

	'th' => '<th>{{item}}</th>',

	'tr' => '<tr>
<td>{{username}}</td>
<td>{{email}}</td>
<td>{{email_link}}</td>
</tr>'

];

// Field render function
// extending the data array: create two new elements in $data
// when 'username' is rendered
$username = function($value, $field, &$data, $options, $isObject) {
	// create new fields in $data
	$data['email'] = htmlspecialchars($value . '@' . $data['domain']);
	$data['email_link'] = '<a href="mailto:' .
		$value . '@' . $data['domain'] . '">' . $data['email'] . '</a>';
	// return username
	return htmlspecialchars($value);
}

// render it
// Please note that the variable $username has NO parentheses ().
echo $engine->render(
	'table',
	[
		'thead' => $engine->each('th', ['Username', 'Email', 'Email link'], "\n"),
		'tbody' => $engine->each('tr', $data, ['username' => $username], "\n")
	]
);
```

This will create the following output:

```html
<table>
<thead>
<tr>
<th>Username</th>
<th>Email</th>
<th>Email link</th>
</tr>
</thead>
<tbody>
<tr>
<td>bob</td>
<td>bob@example.com</td>
<td><a href="mailto:bob@example.com">bob@example.com</a></td>
</tr>
<tr>
<td>mary</td>
<td>mary@example.com</td>
<td><a href="mailto:mary@example.com">mary@example.com</a></td>
</tr>
<tr>
<td>jenny</td>
<td>jenny@example.com</td>
<td><a href="mailto:jenny@example.com">jenny@example.com</a></td>
</tr>
</tbody>
</table>
```

This can also be done otherwise (see next example), by defining two closures for the fields `email` and `email_link`. These functions can create and return the field value without adding it to the data array.

#### Using the generator mode

In the example above, the full table will be rendered in memory. Not a problem with only 3 rows of data, but with hundreds of rows it will probably exceed the memory limit. But fortunately, there is an easy way to circumvent this.

This is where the 5-th parameter comes into play. You can specify a PHP generator function here, that will receive rendered rows on a one-by-one basis. Generators are a relatively new feature in PHP, but they come in handy in situations like this.

We use the same data as above, and the result will be the same. Note the difference in the table template, it is split into two parts and the field `tbody` was dropped. Rendered templates are not accumulated in memory, but directly written to the output via echo.

```php
$templates = [

	'table-start' => '<table>
<thead>
<tr>
{{thead}}
</tr>
</thead>
<tbody>
';

	'table-end' => '
</tbody>
</table>',

	'th' => '<th>{{item}}</th>',

	'tr' => '<tr>
<td>{{username}}</td>
<td>{{email}}</td>
<td>{{email_link}}</td>
</tr>'

];

// Field render functions
$email = function($value, $field, $data, $options, $isObject) {
	return htmlspecialchars($data['username'] . '@' . $data['domain']);
}
$email_link = function($value, $field, $data, $options, $isObject) {
	return '<a href="mailto:' .
		$data['username'] . '@' . $data['domain'] . '">' .
		htmlspecialchars($data['username'] . '@' . $data['domain']) . '</a>';
}

// receive rows one-by-one and write it to the output
// this tiny closure acts as a co-routine to the each-method
$rowGenerator = function() {
	while (true) {
		echo yield;
	}
}

// render the beginning of the table
echo $engine->render(
	'table-start',
	[
		'thead' => $engine->each('th', array('Username', 'Email'))
	]
);
// create table body using the generator function
// No echo here, as 'each' does not return anything in generator mode
$engine->each(
	'tr',
	$data,
	['email' => $email, 'email_link' => $email_link],
	"\n",
	$rowGenerator()
);
// complete the table
// There is no field in this template, so we need only the name parameter
echo $engine->render('table-end');
```

### Using the extended `FileTemplate` class

Templates can also be stored in files and loaded via `FileTemplate`. This class extends `Template` by adding file access features. `FileTemplate` does not alter any of the existing methods, so its fully compatible with the `Template` class.

Three methods are added, `loadTemplates`, `loadParsedTemplates` and `saveParsedTemplates`.

The `loadTemplate` method accepts either a (glob-)filemask or an array of filenames. The second parameter is optional and may specify a directory path. Using the filemask is simpler, but requires a clean naming convention and/or sub-directories to build template sets.

The third parameter may be set to `true`, to add the templates to the internal array.

The other two methods, `loadParsedTemplates` and `saveParsedTemplates`, provides a simple caching mechanism. They make it possible to store *all parsed templates* - JSON encoded - in a single file and reload it later. This may save some milliseconds as there is only one file to read and the templates must not be parsed again.


```php
use AirTemplate\FileTemplate;
$engine = new FileTemplate;
// load templates with a specific filename pattern and add two more
// with specific names using and array of filenames
$engine
	->loadTemplates('test_*.tmpl', './templates')
	->loadTemplates(['form_1.tmpl', 'form_2.tmpl'], './templates', true);

// save and load parsed templates
$engine->saveParsedTemplates('./cache/test_1.json');
$engine->loadParsedTemplates('./cache/test_1.json');
```
### Example Code

You will find some (commented) examples in the [examples directory](./examples). The index.php itself is also an example that renders the menu-page. It uses features, like `each` and file based templates. All examples (except index.php) have simple benchmark tests included, giving some hints about rendering times and memory usage.

Please note that the displayed memory consumption values may have strong variations when compared between different platforms and PHP versions (see discussion on stackoverflow: [PHP memory_get_usage](http://stackoverflow.com/questions/4010781/php-memory-get-usage).

## License

The MIT [License](./LICENSE).