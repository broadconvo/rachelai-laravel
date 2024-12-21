#!/bin/bash

echo "========================================================"
echo "START"
echo "========================================================"
echo " Zsh and Oh My Zsh Setup"
echo "--------------------------------------------------------"
echo "- This script ensures Zsh (a powerful shell) and"
echo "  Oh My Zsh (a popular framework for Zsh) are installed."
echo "- It first checks for existing installations to avoid"
echo "  unnecessary re-installation."
echo "--------------------------------------------------------"
echo " Step 1: Checking Zsh Installation"
echo "--------------------------------------------------------"

if ! command -v zsh &> /dev/null; then
    echo "--- Zsh is not installed. Installing Zsh..."
    apt update
    apt install -y zsh
    echo "--- Zsh has been successfully installed!"
else
    echo "--- Zsh is already installed. Skipping installation."
fi
echo "--------------------------------------------------------"
echo " Step 2: Checking Oh-My-Zsh Installation"
echo "--------------------------------------------------------"

# Check if Oh My Zsh is installed
if [ ! -d "$HOME/.oh-my-zsh" ]; then
    echo "--- Oh My Zsh is not installed."
    echo "--- Installing Oh My Zsh..."
    yes | sh -c "$(curl -fsSL https://raw.githubusercontent.com/ohmyzsh/ohmyzsh/master/tools/install.sh)"
else
    echo "--- Oh My Zsh is already installed. Skipping installation."
fi

# Check if fonts-powerline is installed
if ! dpkg -l | grep -q fonts-powerline; then
    echo "--- Installing fonts-powerline..."
    apt install -y fonts-powerline
else
    echo "--- fonts-powerline is already installed. Skipping installation."
fi

# Check if locales package is installed
if ! dpkg -l | grep -q locales; then
    echo "--- Installing locales..."
    apt install -y locales
    echo "--- Generating locale en_US.UTF-8..."
    locale-gen
    echo 'LANG="en_US.UTF-8"' > /etc/default/locale
    echo 'LC_ALL="en_US.UTF-8"' >> /etc/default/locale
else
    echo "--- Locales package is already installed. Skipping installation."
fi

echo ""
echo "--- Oh-My-Zsh AGNOSTER setup is complete!"

echo ""
echo "--------------------------------------------------------"
echo " Step 3: Set defaults"
echo "--------------------------------------------------------"

echo "--- Starting ZSH Defaults setup..."
echo "--- Set zsh as default shell"
# shellcheck disable=SC2046
chsh -s $(which zsh)

# Step 2: Add default configuration to /home/.zshrc
echo "--- Copy own .zshrc to /root/.zshrc..."
cp /home/site/wwwroot/.azure/.zshrc /root/.zshrc

echo "--- Source /root/.zshrc configuration..."
zsh /home/install/zsh-source.sh

echo ""
echo "--- Zsh configuration setup complete!"
echo "--------------------------------------------------------"
echo "END"
echo "========================================================"
echo ""
