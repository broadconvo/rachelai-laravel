#!/bin/zsh


echo ""
echo "--------------------------------------------------------"
echo " Step 3: Set defaults"
echo "--------------------------------------------------------"

echo "Starting ZSH Defaults setup..."
echo "Set zsh as default shell"
# shellcheck disable=SC2046
chsh -s $(which zsh)

# Define paths
ZSHRC_TARGET="/home/.zshrc"


# Step 2: Add default configuration to /home/.zshrc
echo "Add default configurations to /home/.zshrc..."
cat <<EOL >> "$ZSHRC_TARGET"
!# /bin/zsh
# Default directory for Azure Web App
export PATH=$HOME/bin:$HOME/.local/bin:/usr/local/bin:$PATH
export ZSH="$HOME/.oh-my-zsh"
ZSH_THEME="agnoster"
plugins=(git)
source $ZSH/oh-my-zsh.sh

cd /home/site/wwwroot

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
EOL

echo "Zsh configuration setup complete!"
echo "Source /home/.zshrc configuration..."
source /home/.zshrc
