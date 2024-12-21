#!/bin/zsh

# Default directory for Azure Web App
export PATH=$HOME/bin:$HOME/.local/bin:/usr/local/bin:$PATH
export ZSH="$HOME/.oh-my-zsh"
ZSH_THEME="agnoster"
plugins=(git)
source $ZSH/oh-my-zsh.sh

cd /home/site/wwwroot

# Ensure locale and terminal settings
echo "--- Exporting LANG, LC_ALL, TERM..."
export LANG=en_US.UTF-8
export LC_ALL=en_US.UTF-8
export TERM="xterm-256color"

# Zsh history configuration
export HISTFILE=/home/.zsh_history
export HISTSIZE=10000
export SAVEHIST=10000

# Aliases
alias project='cd /home/site/wwwroot'
alias gpm='cd /home/site/wwwroot && git pull origin main  && echo "Git pull complete!"'
alias art='/usr/local/bin/php /home/site/wwwroot/artisan'
alias tinker='/usr/local/bin/php /home/site/wwwroot/artisan tinker'

prompt_context() {
  return
}
