<?php

namespace App\Agents;

use UseTheFork\Synapse\Agent;

class FaqAgent extends Agent
{
    protected string $promptView = 'Prompts.FaqPrompt';
}
