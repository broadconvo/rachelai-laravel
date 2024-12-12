<?php

namespace App\Agents;

use UseTheFork\Synapse\Agent;

class EmailAgent extends Agent
{
    protected string $promptView = 'Prompts.EmailResponderPrompt';
}
