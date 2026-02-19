import base64
import json
import urllib.request
from pathlib import Path

BASE = "http://localhost:3000"
AUTH = "Basic " + base64.b64encode(b"admin:admin").decode()
HEADERS = {"Authorization": AUTH}

with urllib.request.urlopen(
    urllib.request.Request(f"{BASE}/api/datasources", headers=HEADERS), timeout=20
) as response:
    datasources = json.load(response)

prometheus_ds = next(d for d in datasources if d.get("type") == "prometheus")
prometheus_uid = prometheus_ds["uid"]

dashboard_path = Path(__file__).with_name("dashboard-consumo-recursos.json")
dashboard = json.loads(dashboard_path.read_text(encoding="utf-8"))


def inject_datasource_uid(node):
    if isinstance(node, dict):
        for key, value in node.items():
            if key == "uid" and value == "__PROM_UID__":
                node[key] = prometheus_uid
            else:
                inject_datasource_uid(value)
    elif isinstance(node, list):
        for item in node:
            inject_datasource_uid(item)


inject_datasource_uid(dashboard)

payload = {
    "dashboard": dashboard,
    "overwrite": True,
    "folderId": 0,
    "message": "Create resource consumption dashboard",
}

request = urllib.request.Request(
    f"{BASE}/api/dashboards/db",
    data=json.dumps(payload).encode("utf-8"),
    method="POST",
    headers={**HEADERS, "Content-Type": "application/json"},
)

with urllib.request.urlopen(request, timeout=25) as response:
    result = json.load(response)

print(result.get("status"))
print(result.get("uid"))
print(result.get("url"))
