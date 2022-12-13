fn = st

POST http://localhost:8080/jrpc
Content-Type: application/json
Accept: application/json

< te{{fn}}.json

echo jsonpath