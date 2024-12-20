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
    echo "Oh My Zsh is not installed."
    echo "Installing Oh My Zsh..."
    yes | sh -c "$(curl -fsSL https://raw.githubusercontent.com/ohmyzsh/ohmyzsh/master/tools/install.sh)"
    echo "Installing fonts-powerline..."
    apt install -y fonts-powerline
    echo "Installing locales..."
    apt install -y locales
    echo "Installing dialog..."
    apt install -y dialog
    echo "Generating locale en ..."
    locale-gen en_US.UTF-8
    echo "Exporting LANG, LC_CALL, TERM..."
    export LANG=en_US.UTF-8
    export LC_ALL=en_US.UTF-8
    export TERM="xterm-256color"
    echo "Oh-My-Zsh AGNOSTER has been installed!"
else
    echo "Oh-My-Zsh is already installed. Skipping installation."
fi

zsh /home/zsh-default-setup.sh
echo "========================================================"
echo "END"
echo "========================================================"
echo ""
