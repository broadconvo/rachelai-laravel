<message role="system">
    # Instruction
    Generate a draft response email based on the following message:

    ## Original Email
    {{ $body }}

    @include('synapse::Parts.OutputSchema')
</message>
