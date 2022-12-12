include bootstrap.rest.php

myVar = 42


### My Comment ###
duration
GET /hello/world

assert body == Hello, world
assert status === 200

echo First request duration: {duration}

duration
GET /error
X-My-Header: {{myVar}}
{
    "foo": "bar"
}

assert status === 500

echo Error request duration: {duration}