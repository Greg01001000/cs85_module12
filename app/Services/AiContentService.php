<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiContentService
{
    /**
     * Generate a draft from a title, content type, and tone.
     *
     * Implement this method so that it:
     *   1. Builds a structured prompt using buildPrompt() below.
     *   2. Sends a POST request to config('services.openai.url') . '/chat/completions'
     *      with an "Authorization: Bearer <key>" header from config('services.openai.key').
     *   3. Sends a system message (the role) and a user message (the prompt).
     *   4. On a failed response: log the status and body with Log::error, then throw an Exception.
     *   5. Return the assistant's message content, or 'No output received' if it is missing.
     *
     * @param  string  $title  The topic or headline supplied by the user.
     * @param  string  $type   'blog post', 'meta description', or 'email subject line'.
     * @param  string  $tone   'professional', 'casual', or 'humorous'.
     * @throws \Exception
     */
    public function generateDraft(string $title, string $type = 'blog post', string $tone = 'professional'): string
    {
        $prompt   = $this->buildPrompt($title);
        $response = $this->makeOpenAIRequest($prompt);

        return $this->extractContentFromResponse($response);
    }

    /**
     * Build the prompt text sent to the model.
     *
     * Implement this method so that it:
     *   - States the task clearly using $title.
     *   - Changes what it asks for based on $type: a full post, a one-line meta
     *     description, or a short email subject line, each with a sensible length.
     *   - Reflects $tone in the wording.
     */
    private function buildPrompt(string $title, string $type, string $tone): string
    {
        // TODO: implement per the spec above.
    }
}