# Mikrotik Call-Home Script

# --- Configuration ---
# Set the URL of your call-home server.
:local serverUrl "http://your-call-home-server.com/public/index.php"
# Set the API Key for this router (leave blank for first check-in).
:local apiKey ""
# Set the IP address or domain of your call-home server for the firewall rule.
:local serverAddress "your-call-home-server.com"
# ---------------------

# Get router information
:local serialNumber [/system routerboard get serial-number]
:local routerModel [/system routerboard get model]

# Build the call-home URL
:local callHomeUrl "$serverUrl?serial=$serialNumber&model=$routerModel&api_key=$apiKey"

# Call home to register or update the router
/tool fetch url=$callHomeUrl keep-result=no

# --- Firewall Rule for SSH Access ---

# Add a comment to identify the firewall rule
:local ruleComment "Allow SSH from Call-Home Server"

# Check if a rule with this comment already exists to avoid duplicates
:if ([/ip firewall filter find comment=$ruleComment] = "") do={
    /log info "Adding firewall rule to allow SSH from $serverAddress."
    /ip firewall filter add action=accept chain=input protocol=tcp dst-port=22 src-address=$serverAddress comment=$ruleComment
} else {
    /log info "Firewall rule for call-home server already exists."
}
