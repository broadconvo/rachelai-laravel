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
echo "Set Zsh as the default shell"
# shellcheck disable=SC2046
chsh -s $(which zsh) $USER
echo ""
echo "Add the following lines to the end of the ~/.zshrc file"
echo 'cd /home/site/wwwroot' >> ~/.zshrc
echo ""
echo "Setting Zsh history file under /home"
export HISTFILE=/home/.zsh_history
echo "Setting Zsh history size"
export HISTSIZE=10000
echo "Setting Zsh history file size"
export SAVEHIST=10000
echo "Creating Zsh history file"
touch /home/.zsh_history
echo "======================================================== END"
