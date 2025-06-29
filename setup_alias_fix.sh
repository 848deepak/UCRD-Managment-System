#!/bin/bash

# Get the absolute path of the UCRD Management System directory
UCRD_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Create launcher script in /usr/local/bin if possible
if [ -d "/usr/local/bin" ] && [ -w "/usr/local/bin" ]; then
    # Create the launcher script for ucrdm
    cat > /usr/local/bin/ucrdm << EOF
#!/bin/bash
cd "$UCRD_PATH" && ./ucrdm
EOF
    chmod +x /usr/local/bin/ucrdm
    
    # Create the launcher script for stopucrdm
    cat > /usr/local/bin/stopucrdm << EOF
#!/bin/bash
cd "$UCRD_PATH" && ./stopucrdm
EOF
    chmod +x /usr/local/bin/stopucrdm
    
    echo "Success! Commands installed in /usr/local/bin."
    echo "You can now use 'ucrdm' and 'stopucrdm' from anywhere."
else
    echo "Cannot write to /usr/local/bin. Using alternate method with shell aliases."
    
    # Determine which shell the user is using
    SHELL_NAME=$(basename "$SHELL")
    SHELL_PROFILE=""
    
    if [ "$SHELL_NAME" = "bash" ]; then
        SHELL_PROFILE="$HOME/.bashrc"
    elif [ "$SHELL_NAME" = "zsh" ]; then
        SHELL_PROFILE="$HOME/.zshrc"
    else
        echo "Unsupported shell: $SHELL_NAME"
        exit 1
    fi
    
    # Remove any existing UCRD aliases
    if grep -q "UCRD Management System" "$SHELL_PROFILE"; then
        # Delete section with aliases
        sed -i.bak '/UCRD Management System/,+2d' "$SHELL_PROFILE"
    fi
    
    # Create simple shell function based aliases
    echo "" >> "$SHELL_PROFILE"
    echo "# UCRD Management System commands" >> "$SHELL_PROFILE"
    echo "function ucrdm() { cd '$UCRD_PATH' && ./ucrdm; }" >> "$SHELL_PROFILE"
    echo "function stopucrdm() { cd '$UCRD_PATH' && ./stopucrdm; }" >> "$SHELL_PROFILE"
    
    echo "Success! Functions added to $SHELL_PROFILE."
    echo "Please run 'source $SHELL_PROFILE' to use the commands."
fi

echo ""
echo "====================================="
echo "  UCRD Management System Setup Complete"
echo "====================================="
echo ""
echo "You can now use these commands from anywhere:"
echo "  ucrdm       - Start the UCRD Management System"
echo "  stopucrdm   - Stop the UCRD Management System"
echo "" 