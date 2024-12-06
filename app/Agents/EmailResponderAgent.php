<?php

namespace App\Agents;

use UseTheFork\Synapse\Agent;

class EmailResponderAgent extends Agent
{
    protected string $promptView = 'Prompts.EmailResponderPrompt';
}
