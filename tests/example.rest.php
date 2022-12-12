POST http://localhost:8080/jrpc
Accept: application/json

{
    "jsonrpc": "2.0",
    "method": "auth.login",
    "params": {
        "email": "shimon@bbumgames.com",
        "password": "12345678"
    },
    "id": 1
}

echo header Authorization

assert status === 200