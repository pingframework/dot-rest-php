assert jsonpath $.results[*], exists === true
assert jsonpath $.results[*], all isArray
assert jsonpath $.results[*], count === 1
assert jsonpath $.results[0].id, exists === true
assert jsonpath $.results[0].id isInt
assert jsonpath $.results[0].id === 42
assert jsonpath $.results[0].bool_var isBool
assert jsonpath $.results[0].bool_var === true
assert jsonpath $.results[0].float_var isFloat
assert jsonpath $.results[0].float_var === 3.14
assert jsonpath $.results[0].string_var isString
assert jsonpath $.results[0].string_var === foo
assert jsonpath $.results[0].null_var === null
assert jsonpath $.results[0].array_var, all isArray
assert jsonpath $.results[0].array_var[0] === 1
assert jsonpath $.results[0].string_var contains foo
assert jsonpath $.results[0].string_var startsWith foo
assert jsonpath $.results[0].string_var endsWith foo
assert jsonpath $.results[0].string_var regex /foo/
assert jsonpath $.results[0].sha256_var sha256 a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3
assert jsonpath $.results[0].md5_var md5 202cb962ac59075b964b07152d234b70

assert jsonpath $.results[0].not_exists, exists === false
assert jsonpath $.results[0].not_exists, exists === true

