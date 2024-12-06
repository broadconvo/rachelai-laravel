<?php

namespace App\Agents;

use UseTheFork\Synapse\Agent;

class TranslatorAgent extends Agent
{
    protected string $promptView = 'Prompts.TranslatorPrompt';
}
