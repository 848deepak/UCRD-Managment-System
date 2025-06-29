#!/bin/bash

# Get the absolute path of the UCRD Management System directory
UCRD_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Determine which shell the user is using
SHELL_NAME=$(basename "$SHELL")
SHELL_PROFILE=""

if [ "$SHELL_NAME" = "bash" ]; then
    SHELL_PROFILE="$HOME/.bashrc"
elif [ "$SHELL_NAME" = "zsh" ]; then
    SHELL_PROFILE="$HOME/.zshrc"
else
    echo "Unsupported shell: $SHELL_NAME"
    echo "Please manually add the following lines to your shell profile:"
    echo "alias ucrdm='cd \"$UCRD_PATH\" && ./ucrdm'"
    echo "alias stopucrdm='cd \"$UCRD_PATH\" && ./stopucrdm'"
    exit 1
fi

# Check if the aliases already exist
CHANGES_MADE=false

if grep -q "alias ucrdm=" "$SHELL_PROFILE"; then
    # Remove old alias
    sed -i '' '/alias ucrdm=/d' "$SHELL_PROFILE"
    CHANGES_MADE=true
fi

if grep -q "alias stopucrdm=" "$SHELL_PROFILE"; then
    # Remove old alias
    sed -i '' '/alias stopucrdm=/d' "$SHELL_PROFILE"
    CHANGES_MADE=true
fi

# Add new aliases
echo "" >> "$SHELL_PROFILE"
echo "# UCRD Management System aliases" >> "$SHELL_PROFILE"
echo "alias ucrdm=\"cd \\\"$UCRD_PATH\\\" && ./ucrdm\"" >> "$SHELL_PROFILE"
echo "alias stopucrdm=\"cd \\\"$UCRD_PATH\\\" && ./stopucrdm\"" >> "$SHELL_PROFILE"

echo "Aliases updated in $SHELL_PROFILE."
echo "Please run 'source $SHELL_PROFILE' or restart your terminal for the changes to take effect."

echo ""
echo "====================================="
echo "  UCRD Management System Setup Complete"
echo "====================================="
echo ""
echo "You can now use these commands from anywhere:"
echo "  ucrdm       - Start the UCRD Management System"
echo "  stopucrdm   - Stop the UCRD Management System"
echo ""
echo "This will automatically:"
echo "  1. Navigate to the UCRD Management System directory"
echo "  2. Start/stop the PHP development server"
echo ""
echo "Access the application at: http://localhost:9000" 