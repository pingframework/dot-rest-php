include bootstrap.rest.php

GET /hello/world

assert status === 200
assert body == Hello, world


GET /hello/world2

assert status === 200
assert body == Hello, world2


GET /error

assert status === 200
