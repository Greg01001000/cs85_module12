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
     *   2. Sends a POST request to config('services.gemini.url')
     *      with an API key header from config('services.gemini.key').
     *   3. Sends the prompt as a user message inside a 'contents' array, where each message contains a 'parts'
     *      array with the text content.
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
        $prompt = $this->buildPrompt($title, $type, $tone);

        try {
            $response = Http::withHeaders([
                'x-goog-api-key' => config('services.gemini.key'),
                'Content-Type'   => 'application/json',
            ])->timeout(30)
            ->post(config('services.gemini.url') . '/models/' . config('services.gemini.model') . ':generateContent', [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'temperature'     => 0.7,
                    'maxOutputTokens' => 1000,
                ],
            ]);

            // Check for HTTP-level failures
            if (!$response->successful()) {
                Log::error('Gemini request failed. ', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                throw new \Exception('The AI request failed. ');
            }

            // Extract content using null coalescing in case of missing keys
            $content = trim($response['candidates'][0]['content']['parts'][0]['text'] ?? '');

            if (empty($content)) {
                throw new \Exception('No output received. ');
            }

            return $content;

        } catch (\Throwable $e) {
            Log::error('Gemini API communication failed. ', [
                'error'         => $e->getMessage(),
                'prompt_length' => strlen($prompt),
            ]);
            throw new \Exception('Failed to communicate with AI service: ' . $e->getMessage());
        }
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
        return match ($type) {
            'blog post' => "You are a skilled tech blogger who writes engaging, informative content for developers
                and technology enthusiasts. Your writing style is conversational yet professional, and
                you excel at making complex topics accessible to a broad audience.
                Task: Write a complete blog post draft based on the title: \"{$title}\"
                Requirements:
                - Length: 400-600 words
                - Include an engaging introduction that hooks the reader
                - Provide 2-3 main sections with clear subheadings
                - Include practical examples or code snippets when relevant
                - End with a conclusion that summarizes key points
                - Use a tone that is {$tone} and informative but approachable
                Format the response as clean markdown with proper headings and structure.",
            'meta description' => "You are an SEO expert who writes compelling meta descriptions.
                Task: Write a meta description for a blog post titled: \"{$title}\"
                Requirements:
                - Length: 150-160 characters
                - Include relevant keywords
                - Be compelling and clickable
                - Accurately describe the content
                - Use a tone that is {$tone}.",
            'email subject line' => "You are an email marketing expert who writes compelling subject lines.
                Task: Write ONE email subject line for a blog post titled: \"{$title}\"
                Requirements:
                - Length: 40-60 characters
                - Include relevant keywords
                - Be compelling and clickable
                - Accurately reflect the content
                - Use a tone that is {$tone}.
                - Return ONLY the subject line text, no numbering, no alternatives, no explanation.",
            default => "You are an ancient Greek philosopher who also knows English and modern technology.
                Task: Rephrase this topic, \"{$title}\", into philosophical terms
                Requirements:
                - Length: No more than twice the length of \"{$title}\"
                - Write in a lively, dramatic style and tone"
        };
    }
}