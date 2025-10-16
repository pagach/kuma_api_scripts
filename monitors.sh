#/bash/bin

TOKEN=$(curl -X POST -L 'http://127.0.0.1:8000/login/access-token/'   -H 'Content-Type: application/x-www-form-urlencoded'   --data 'username=admin&password=admin' http://127.0.0.1:8000/login/access-token/ | jq -r ".access_token")

curl -L -H 'Accept: application/json' -H "Authorization: Bearer ${TOKEN}" http://127.0.0.1:8000/monitors/
