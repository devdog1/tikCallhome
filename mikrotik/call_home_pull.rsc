# Mikrotik Initial Adoption Script for PULL Method (RouterOS v7)

# --- Configuration ---
:local serverUrl "https://tikman.westmancom.com/adopt_script.php"
:local adoptionPsk "eW0kp3jlqPT3np0llufHzoNKyo1LW4YY"
# ---------------------

# Get router information
:local serialNumber [/system routerboard get serial-number]
:local routerModel [/system routerboard get model]

# Build call-home URL
:local callHomeUrl "$serverUrl?serial=$serialNumber&model=$routerModel&psk=$adoptionPsk"

# Local filename for fetched script
:local fetchFile "adopt_download.rsc"

# Remove any old copy
/file remove [find name=$fetchFile]

# Fetch from server
/tool fetch url=$callHomeUrl mode=https dst-path=$fetchFile

# Check if file exists and has size > 0
:if ([:len [/file find name=$fetchFile]] > 0) do={
:local fsize [/file get $fetchFile size]
:if ($fsize > 0) do={
:log info "Adoption script downloaded successfully. Executing..."
/import $fetchFile
} else={
:log warning "Downloaded adoption script is empty. Nothing executed."
}
} else={
:log warning "Adoption script download failed. File not present."
}