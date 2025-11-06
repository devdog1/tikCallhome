# Mikrotik Call-Home Script

# --- Configuration ---
# Set the URL of your call-home server.
:local serverUrl "%%ADOPT_SCRIPT_URL%%"
# Set the API Key for this router (leave blank for first check-in).
:local apiKey ""
# ---------------------

# Get router information
:local serialNumber [/system routerboard get serial-number]
:local routerModel [/system routerboard get model]

# Build the call-home URL
:local callHomeUrl "$serverUrl?serial=$serialNumber&model=$routerModel&api_key=$apiKey"

# Call home to register or update the router
/tool fetch url=$callHomeUrl keep-result=no mode=https
