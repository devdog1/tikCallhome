# Mikrotik Call-Home Script for PULL Method

# --- Configuration ---
# Set the URL of your call-home server.
:local serverUrl "https://your-call-home-server.com/public/"
# Set the API Key for this router (leave blank for first check-in).
:local apiKey ""
# Set the IP address or domain of your call-home server for the firewall rule.
:local serverAddress "your-call-home-server.com"
# ---------------------

# Get router information
:local serialNumber [/system routerboard get serial-number]
:local routerModel [/system routerboard get model]

# --- Call Home to Register ---
:local callHomeUrl "$serverUrl/index.php?serial=$serialNumber&model=$routerModel&api_key=$apiKey"
/tool fetch url=$callHomeUrl keep-result=no

# --- Firewall Rule for SSH Access (Optional, but recommended) ---
:local ruleComment "Allow SSH from Call-Home Server"
:if ([/ip firewall filter find comment=$ruleComment] = "") do={
    /log info "Adding firewall rule to allow SSH from $serverAddress."
    /ip firewall filter add action=accept chain=input protocol=tcp dst-port=22 src-address=$serverAddress comment=$ruleComment
}

# --- Fetch and Run Command Script ---
:local scriptUrl "$serverUrl/generate_script.php?serial=$serialNumber&api_key=$apiKey"
:local scriptName "commands.rsc"

# Fetch the script
/tool fetch url=$scriptUrl dst-path=$scriptName mode=http

# If the script was downloaded, import it
:if ([:len [/file find name=$scriptName]] > 0) do={
    /log info "Downloaded new command script, importing..."
    /import $scriptName
    /file remove $scriptName
    /log info "Script import complete."
} else {
    /log info "No new command script downloaded."
}
