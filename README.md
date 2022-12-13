# What is Dot Rest PHP?
Dot Rest PHP is a tool that simplifies the process of testing and interacting with HTTP-based APIs. It allows users to
write simple plain text files to execute HTTP requests and test or retrieve the resulting data. Some of its key features
include native PHP code execution, external file inclusion, chaining HTTP requests, and capturing data from response
body, headers, and cookies using JSONPath and XPath expressions. It also includes an "assert" directive for testing
responses using regex, JSONPath, and XPath expressions.

## Example:

```text
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

# Now call get users endpoint using grabbed JWT token and user id
GET https://example.com/api/v1/users/{{user_id}}
Authorization: {{token}}

# Assert that the response status code is 200
assert status == 200
```

# Resources
* [Installation](https://github.com/pingframework/dot-rest-php/wiki/Installation)
* [Documentation](https://github.com/pingframework/dot-rest-php/wiki)
* [License: MIT](https://github.com/pingframework/dot-rest-php/blob/master/LICENSE)
