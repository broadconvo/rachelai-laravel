#!/bin/bash

echo "======================================================== START"
echo " Zsh and Oh My Zsh Setup"
echo "--------------------------------------------------------"
echo "- This script ensures Zsh (a powerful shell) and"
echo "  Oh My Zsh (a popular framework for Zsh) are installed."
echo "- It first checks for existing installations to avoid"
echo "  unnecessary reinstallation."
echo "========================================================"

echo ""
echo "========================================================"
echo " Step 1: Checking Zsh Installation"
echo "--------------------------------------------------------"
echo "- Zsh is a feature-rich alternative to Bash."
echo "- This step verifies if Zsh is already installed."
echo "========================================================"

if ! command -v zsh &> /dev/null; then
    echo "Zsh is not installed. Installing Zsh..."
    apt update
    apt install -y zsh
    echo "Zsh has been successfully installed!"
else
    echo "Zsh is already installed. Skipping installation."
fi

echo ""
echo "========================================================"
echo " Step 2: Checking Oh My Zsh Installation"
echo "--------------------------------------------------------"
echo "- Oh My Zsh is a framework for managing Zsh configurations."
echo "- This step verifies if Oh My Zsh is already installed."
echo "========================================================"

if [ ! -d "$HOME/.oh-my-zsh" ]; then
    echo "Oh My Zsh is not installed. Installing Oh My Zsh..."
    yes | sh -c "$(curl -fsSL https://raw.githubusercontent.com/ohmyzsh/ohmyzsh/master/tools/install.sh)"
    echo "Oh My Zsh has been successfully installed!"
else
    echo "Oh My Zsh is already installed. Skipping installation."
fi

echo ""
echo "======================================================== END"
echo " Zsh and Oh My Zsh setup complete!"
echo "--------------------------------------------------------"
echo "- Enjoy using Zsh with the power and flexibility"
echo "  of Oh My Zsh on your system."
echo "========================================================"
