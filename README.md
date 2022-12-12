# What is Dot Rest PHP?

Dot Rest PHP is a tool that simplifies the process of testing and interacting with HTTP-based APIs. With Dot Rest PHP,
users can write simple plain text files to execute HTTP requests and test or retrieve the resulting data.

## Main features

- Simple plain text format
- Native PHP code execution
- External files inclusion
- Chaining HTTP requests
- Capturing data from response body, headers and cookies using JSONPath and XPath expressions
- Testing responses using "assert" directive and regex, JSONPath and XPath expressions

One of the key features of Dot Rest PHP is its support for native PHP code execution. This allows users to write custom
PHP code to manipulate or process the data received from an API. Additionally, Dot Rest PHP allows users to include
external files in their requests, which can be useful for sharing common code between different request files.

Dot Rest PHP also supports chaining HTTP requests, which means that users can write a series of requests that depend on
each other and execute them in a single command. This can be useful for testing complex scenarios or for building
integrations between different APIs.

Another powerful feature of Dot Rest PHP is its support for capturing data from HTTP responses. Using JSONPath and XPath
expressions, users can extract specific data from the response body, headers, or cookies and store it for use in
subsequent requests. This can be useful for extracting session tokens or other data that needs to be included in
subsequent requests.

Finally, Dot Rest PHP includes an "assert" directive that allows users to test the data received from an API using
regex, JSONPath, or XPath expressions. This can be used to ensure that the data received from an API is correct, or to
verify that the API is returning the expected results.

## Example - HTTP client mode:

```http request
# Call login first
POST https://example.com/api/v1/login
{
    "username": "admin",
    "password": "admin"
}

# Grab JWT token from the headers
token = header Authorization
# Grab user id from the json response body
user_id = jsonpath $.result.user.id

# Now call API to get the user using grabbed JWT token and user id
GET https://example.com/api/v1/users/{{user_id}}
Authorization: {{token}}
```

> In "http client mode," Dot Rest PHP returns the latest response body
> from the executed HTTP request. This is the actual data that is returned by the server
> in response to the request, and can be used by the user to extract/retrieve
> and process the information they are interested in.

## Example - Testing mode:

```http request
# Call the login endpoint first to get the JWT token
POST https://example.com/api/v1/login
{
    "username": "admin",
    "password": "admin"
}

# Assert that the response status code is 200
assert status == 200
# Assert that the response body contains the token
assert jsonpath $.result.token, exists == true
# Assert that the response body contains the user id
assert jsonpath $.result.user.id, exists == true

# Grab the token from the headers
token = header Authorization
# Grab the user id from the response body (json)
user_id = jsonpath $.result.user.id

# Now call the API to get the user using grabbed JWT token and user id
GET https://example.com/api/v1/users/{{user_id}}
Authorization: {{token}}

# Assert that the response status code is 200
assert status == 200
```

> In testing mode, Dot Rest PHP provides a summary of the assertions made during
> the execution of the HTTP request. This summary can be used to evaluate
> the accuracy of the request and ensure that it is functioning as intended.
> The assertion summary provides information about the success or failure of each assertion,
> along with any error messages or other relevant details.

# ToC

1. [Installation](#installation)
1. [Usage](#usage)
1. [File format](#file-format)
    1. [Value Object](#value-object)
    1. [Request](#request)
        1. [Methods](#request-methods)
        1. [URI](#request-uri)
        1. [Headers](#request-headers)
        1. [Cookies](#request-cookies)
        1. [Options](#request-options)
        1. [Form and Multipart body](#form-and-multipart-body)
        1. [Uploading files](#uploading-files)
        1. [Json body](#json-body)
        1. [Plain text body](#plain-text-body)
    1. [Response](#response)
        1. [Extracting Cookies](#extracting-cookies)
        1. [Extracting Headers](#extracting-headers)
        1. [Extracting data with jsonpath](#extracting-data-with-jsonpath)
        1. [Extracting data with xpath](#extracting-data-with-xpath)
    1. [Asserting](#asserting)
    1. [Including](#including)
    1. [Variables and functions](#variables-and-functions)
    1. [Placeholders](#placeholders)
    1. [Configuration](#configuration)
    1. [Environment](#environment)
    1. [Duration/Timer](#durationtimer)
    1. [Comments](#comments)
    1. [Echo](#echo)
    1. [Code Block](#code-block)
1. [License](#license)

## Installation

Installation is very easy (thanks to composer ;)):

(add to require or require-dev section in your composer.json file)

> "pingframework/dot-rest-php": "*"

You should choose last stable version, wildcard char ("*") is only an example.

Or install it globally:

```shell
composer global require "pingframework/dot-rest-php=*"
```

> **NOTE!** The minimum required PHP version is 8.1.0

## Usage

Dot Rest PHP can be used in two main ways:

### HTTP client mode

In "HTTP client" mode, Dot Rest PHP executes all the directives defined in the specified file and returns the latest
response body. This mode is the default behavior of the tool, and is useful for retrieving data from a server or
performing other tasks using HTTP requests.

If an error occurs during the execution of the request, Dot Rest PHP will return an error message and exit with a
non-zero exit code by default. This behavior can be changed by passing the --conf failOnAssertionError=true option when
running the tool. This will cause Dot Rest PHP to continue running even if an error occurs, allowing users to continue
processing the response body and potentially recover from the error. However, this can also result in additional errors
or unexpected behavior if the error is not properly handled.

### Testing mode

In "testing tool" mode, Dot Rest PHP functions similarly to "HTTP client" mode, but instead of returning the response
body, it returns a summary of the assertions made during the execution of the HTTP request. This mode can be used to
evaluate the accuracy of the request and ensure that it is functioning as intended. The assertion summary provides
information about the success or failure of each assertion, along with any error messages or other relevant details.
This information can be used to identify and resolve any issues with the HTTP request, and ensure that it is working
properly.
> By the adding verbosity flag (-v) you can get more information about the execution.
> * -v Verbode mode
> * -vv Very verbose mode
> * -vvv Debug mode

## File format

The Dot Rest PHP file is a collection of directives that are used to define HTTP requests, assertions, variable
declarations, configuration options, PHP code blocks, and more. Each directive is a single or multi-line piece of text
that begins with a directive name or pattern. Directive names are case-sensitive.
The following directives are supported:

- [Request](#request) - defines an HTTP request
- [Assert](#asserting) - defines an assertion
- [Include](#including) - includes another file
- [Var](#variables-and-functions) - defines a variable
- [Config](#configuration) - defines a configuration option
- [Code Block](#code-block) - defines a PHP native code block
- [Echo](#echo) - prints a message to the console
- [Duration](#durationtimer) - starts a timer
- [Comment](#comments) - adds a comment to the file

### Value Object

The value object is a string token that can represent various types of data, such as strings, numbers, booleans, arrays,
null, or even file content. It can also be used to represent a variable or function call. Each directive uses a value
object to define its value. For example, the request directive uses a value object to define the request headers,
options, and body, and the assert directive uses a value object to define the left and right operands in an assertion.

#### Examples:

```text
# Variable reference
myVar = {{value}}

# Integer 
myVar = 42

# Float 
myVar = 4.2

# String
myVar = my str

# String including placeholders
myVar = my {{myVar42}} str

# Boolean
myVar = true
myVar = false

# Null 
myVar = null

# Array (each element is also a Value Object)
myVar = [1, 4.2, "str", true, false, null]
myVar = [key1 => value1, key2 => value2]

# File content
myVar = < path/to/file.txt
myVar = < path/{{placeholder42}}/file.txt

# Function call
myVar = env MY_ENV_VAR
myVar = jsonpath $.result.items[0].id, count

# Function call wrapped in curly braces
myVar = {config baseUri}
```

### Request

The directive begins with a mandatory METHOD and URI line, following by optional header lines in "key: value" format.

Then optional [OPTIONS] section can be used to define request options {@see [Request Options](#request-options)
section}.

Finally, the optional request body can be defined.

> NOTE! All sections are supported placeholders {@see [Placeholders](#placeholders) section}.

#### Example:

```http request
POST https://example.com/api/v1/users/update
Content-Type: application/json
Authorization: Bearer {{token}}

{
    "id": {env USER_ID},
    "name": "John Doe",
    "email": "john@example.com"
}
```

#### Structure

| Section                    | Description                              |
|:---------------------------|------------------------------------------|
| POST /api/v1/users/update  | HTTP method name and URI (mandatory)     |
| Content-Type: ...          | HTTP header (optional)                   |
| Authorization: ...         | HTTP header (optional)                   |
| [OPTIONS]                  | Request options section begin (optional) |
| timeout: 10                | in key: value format                     |
| ...                        |                                          |
| [FORM]                     | Form params body (optional)              |
| field1: value1             | in "key: value" format                   |
| ...                        |                                          |
| [MULTIPART]                | OR Multipart form data (optional)        |
| name: ...                  | Field name (mandatory)                   |
| contents: < path/to/f1.txt | Field value (mandatory)                  |
| filename: f1.txt           | Filename (optional)                      |
| [MULTIPART]                | Second multipart section                 |
| ...                        |                                          |
| {                          | OR Json body (optional)                  |
| "id": 1,                   |                                          |
| "name": "John Doe",        |                                          |
| }                          |                                          |
| ...                        |                                          |
| my plain text              | OR Plain Text body (optional)            |

#### Request Methods

Request method is a string that defines the HTTP method to use for the request.
The following methods are supported:

- GET
- POST
- PUT
- PATCH
- DELETE
- HEAD
- OPTIONS

#### Request URI

Request URI is a string that defines the URI to use for the request. The URI can be a relative or absolute URL,
and can contain placeholders {@see [Placeholders](#placeholders) section}. If the URI is relative, it will be
resolved relative to the base URL defined in the configuration {@see [Configuration](#configuration) section}.

#### Request Headers

Headers, if present, follow directly after the method and URL line and represents
the HTTP headers to send with the request. Each header is defined on a separate line in the "key: value" format
and can contain placeholders {@see [Placeholders](#placeholders) section}.

##### Example:

```http request
POST https://example.com/api/v1/users/update
Content-Type: application/json
X-My-Header: {{my_header}}
X-My-Other-Header: {env MY_OTHER_HEADER}
```

#### Request Cookies

Cookies can be included in the request headers using the "Cookie" header. The value of the header should be a string
with the cookies to send with the request in the format "key=value". Multiple cookies can be separated by semicolons or
set on different lines. Placeholders can also be used in the cookies {@see [Placeholders](#placeholders) section}.

##### Example:

```http request
POST https://example.com/api/v1/users/update
Cookie: session_id=123; user_id=456
Cookie: token={{token}}
```

#### Request Options

The request options section, if present, follows directly after the headers and represents the options to use for
the request. Each option is defined on a separate line in the "key: value" format and can contain placeholders
{@see [Placeholders](#placeholders) section}.

Dot Rest PHP utilizes the [guzzle http client](https://github.com/guzzle/guzzle) library and passes all options to the
client object. Therefore, Dot Rest PHP supports all options available through the client
{@see [detailed documentation](https://docs.guzzlephp.org/en/stable/request-options.html)}.

#### Form and Multipart body

Dot Rest PHP supports 4 types of body: form, multipart, json, or plain text, but only one type can be used at a
time. Form and multipart body can be defined using the [FORM] and [MULTIPART] sections, respectively. These sections are
defined in the "key: value" format as an associative array. The value can be a string or a placeholder as defined in the
Placeholders section.

The multipart section can be repeated to define multiple fields and must contain the following fields:

- name: the field name (mandatory)
- contents: the field value (mandatory)
- filename: the optional filename

> NOTE: If the form type is used, the Content-Type header will be set to application/x-www-form-urlencoded if no
> Content-Type header is already present.

> NOTE: If the multipart type is used, the Content-Type header will be set to multipart/form-data if no Content-Type
> header is already present.

> NOTE! Empty lines are ignored during the parsing process.

##### Example form body:

```http request
POST https://example.com/api/v1/users/update
[FORM]
id: {env USER_ID}
name: John Doe
email: {{email}}
```

##### Example multipart body:

```http request
POST https://example.com/api/v1/users/update

[MULTIPART]
name: id
contents: {env USER_ID}
[MULTIPART]
name: name
contents: John Doe
[MULTIPART]
name: email
contents: {{email}}

[MULTIPART]
name: file1
contents: < path/to/f1.txt
filename: f1.txt
```

#### Uploading files

To upload a file using the multipart body type, you can use the "<" character followed by the path to the file. The
Content-Type header will be automatically set to "multipart/form-data" if no Content-Type header is already present.
This ensures that the file is properly encoded and sent to the server.

##### Example:

```http request
POST https://example.com/api/v1/upload
[MULTIPART]
name: file1
contents: < path/to/f1.txt
filename: f1.txt
```

> NOTE! The file path can contain placeholders {@see [Placeholders](#placeholders) section}.

#### Json body

To define a json body, you can use the curly brackets "{" and "}" to define the json body. The Content-Type header will
be automatically set to "application/json" if no Content-Type header is already present.

##### Example - json body as a plain text:

```http request
POST https://example.com/api/v1/users/update
{
    "id": {env USER_ID},
    "name": "John Doe",
}
```

##### Example - the body taken from file:

```http request
myFileName = f1

POST https://example.com/api/v1/users/update
Content-Type: application/json

< path/to/{{myFileName}}.json
```

In this example, the body string will be taken from the file "path/to/f1.json".

> NOTE! If file content is used as a body string, the header Content-Type: application/json could not be automatically
> detected, so, you have to set it manually.

#### Plain text body

All text lines defined after the request headers/options will be used as a plain text body up to next token.

##### Example:

```http request
POST https://example.com/api/v1/users/update
Content-Type: multipart/form-data; boundary=boundary

--boundary
Content-Disposition: form-data; name="file"; filename="file.csv"

// The 'file.csv' file will be uploaded
{< ../examples/file.csv}
--boundary
Content-Disposition: form-data; name="advertType"

1
--boundary

```

> NOTE! Placeholders are supported inside body string {@see [Placeholders](#placeholders) section}.

### Response

TBD...

#### Extracting Cookies

TBD...

#### Extracting Headers

TBD...

#### Extracting data with jsonpath

TBD...

#### Extracting data with xpath

TBD...

### Asserting

TBD...

### Including

TBD...

### Variables and functions

TBD...

#### Data Types

TBD...

### Placeholders

TBD...

### Configuration

TBD...

### Environment

TBD...

### Duration/Timer

TBD...

### Comments

TBD...

### Echo

TBD...

### Code Block

TBD...

# License

TBD...
