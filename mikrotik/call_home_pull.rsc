# Mikrotik Initial Adoption Script for PULL Method

# --- Configuration ---
# These variables will be replaced by the server when the script is downloaded.
:local serverUrl "%%ADOPT_SCRIPT_URL%%"
:local adoptionPsk "%%ADOPTION_PSK%%"
# ---------------------

# Get router information
:local serialNumber [/system routerboard get serial-number]
:local routerModel [/system routerboard get model]

# --- Call Home to Register ---
# This first call is keyless, but includes a pre-shared key. The server will respond
# with a new script that includes the API key for this router.
:local callHomeUrl "$serverUrl?serial=$serialNumber&model=$routerModel&psk=$adoptionPsk"
/tool fetch url=$callHomeUrl keep-result=no mode=https
