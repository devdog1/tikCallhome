# Mikrotik Initial Adoption Script for PULL Method

# --- Configuration ---
# This URL will be replaced by the server with the correct adoption script URL.
:local serverUrl "%%ADOPT_SCRIPT_URL%%"
# ---------------------

# Get router information
:local serialNumber [/system routerboard get serial-number]
:local routerModel [/system routerboard get model]

# --- Call Home to Register ---
# This first call is keyless. The server will respond with a new script
# that includes the API key for this router.
:local callHomeUrl "$serverUrl?serial=$serialNumber&model=$routerModel"
/tool fetch url=$callHomeUrl keep-result=no mode=https
