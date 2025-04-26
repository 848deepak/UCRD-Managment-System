#!/bin/bash

# Get the absolute path of the UCRD Management System directory
UCRD_PATH=$(pwd)
echo "UCRD path: $UCRD_PATH"

# Fix the ucrdm command
sudo tee /usr/local/bin/ucrdm > /dev/null << EOF
#!/bin/bash
cd "${UCRD_PATH}" || exit 1
./ucrdm
EOF
sudo chmod +x /usr/local/bin/ucrdm

# Fix the stopucrdm command
sudo tee /usr/local/bin/stopucrdm > /dev/null << EOF
#!/bin/bash
cd "${UCRD_PATH}" || exit 1
./stopucrdm
EOF
sudo chmod +x /usr/local/bin/stopucrdm

echo "Commands fixed! Try using 'ucrdm' and 'stopucrdm' in a new terminal window." 