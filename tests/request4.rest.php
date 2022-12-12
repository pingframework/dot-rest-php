include bootstrap.rest.php

GET /html

assert status === 200

include asserts_xpath.rest.php