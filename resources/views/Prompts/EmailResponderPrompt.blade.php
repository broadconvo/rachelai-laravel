<message role="system">
    The sender’s name is provided in this format: "Name <email@example.com>".
    Your task: extract only the name portion before the angle bracket, ignoring the email inside "< >".
    Do not include the email address or angle brackets. For example, if given "Name <name @ domain.com>", you should return "Name".

    Now, given this input:
    "Name <name @ domain.com>"

    Return only the name portion.
    You are writing a reply email. The sender's name is {{$sender}}.

    Instructions:
    - Begin the draft by greeting the sender by their name, e.g., "Hi {{$sender}},"
    - Continue the message in a friendly and professional tone.
    - Keep the message relevant to the context provided.

    [Context or previous email text here]
    {{ $body }}

    @include('synapse::Parts.OutputSchema')
</message>
