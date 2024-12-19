#!/bin/bash

echo "======================================================== START"
echo " Zsh and Oh My Zsh Setup"
echo "--------------------------------------------------------"
echo "- This script ensures Zsh (a powerful shell) and"
echo "  Oh My Zsh (a popular framework for Zsh) are installed."
echo "- It first checks for existing installations to avoid"
echo "  unnecessary re-installation."
echo "========================================================"

echo ""
echo "--------------------------------------------------------"
echo " Step 1: Checking Zsh Installation"
echo "--------------------------------------------------------"

if ! command -v zsh &> /dev/null; then
    echo "Zsh is not installed. Installing Zsh..."
    apt update
    apt install -y zsh
    echo "Zsh has been successfully installed!"
else
    echo "Zsh is already installed. Skipping installation."
fi
echo "--------------------------------------------------------"
echo " Step 2: Checking Oh My Zsh Installation"
echo "--------------------------------------------------------"

if [ ! -d "$HOME/.oh-my-zsh" ]; then
    echo "Oh My Zsh is not installed. Installing Oh My Zsh..."
    yes | sh -c "$(curl -fsSL https://raw.githubusercontent.com/ohmyzsh/ohmyzsh/master/tools/install.sh)"
    echo "Oh My Zsh has been successfully installed!"
else
    echo "Oh My Zsh is already installed. Skipping installation."
fi

echo ""
# Define paths
ZSHRC_SOURCE="$HOME/.zshrc"
ZSHRC_PERSISTED="/home/.zshrc"

echo "Starting Zsh configuration setup..."

# Step 1: Backup existing ~/.zshrc to /home/.zshrc
if [ -f "$ZSHRC_SOURCE" ]; then
    echo "Backing up existing ~/.zshrc to /home/.zshrc..."
    cp "$ZSHRC_SOURCE" "$ZSHRC_PERSISTED"
else
    echo "No existing ~/.zshrc found. Creating a new /home/.zshrc..."
    touch "$ZSHRC_PERSISTED"
fi

# Step 2: Add default configuration to /home/.zshrc
echo "Adding default configurations to /home/.zshrc..."
cat <<EOL >> "$ZSHRC_PERSISTED"

# Default directory for Azure Web App
cd /home/site/wwwroot

# Zsh history configuration
export HISTFILE=/home/.zsh_history
export HISTSIZE=10000
export SAVEHIST=10000

# Git pull alias
alias gitpull='cd /home/site/wwwroot && git pull && echo "Git pull complete!"'
EOL

# Step 3: Replace ~/.zshrc with sourcing /home/.zshrc
echo "Replacing ~/.zshrc to source /home/.zshrc..."
echo 'source /home/.zshrc' > "$ZSHRC_SOURCE"

# Step 4: Reload Zsh configuration
echo "Reloading Zsh configuration..."
source "$ZSHRC_SOURCE"

echo "Zsh configuration setup complete!"
echo "======================================================== END"
